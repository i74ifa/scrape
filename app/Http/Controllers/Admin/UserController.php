<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendFcmRequest;
use App\Models\User;
use App\Services\Fcm\Fcm;
use App\Services\Fcm\FcmBody;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->select('id', 'name', 'email', 'phone', 'country_code', 'device_token', 'created_at')
            ->latest();

        if ($request->filled('search')) {
            $term = $request->string('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if ($request->boolean('with_device_only')) {
            $query->whereNotNull('device_token');
        }

        $users = $query->paginate(30)->withQueryString();

        // device_token is sensitive + long — expose only a presence flag and a
        // short mask for the admin UI, never the raw token.
        $users->getCollection()->transform(function (User $u) {
            $u->setAttribute('has_device', filled($u->device_token));
            $u->setAttribute(
                'device_mask',
                filled($u->device_token) ? mb_substr($u->device_token, 0, 8).'…' : null,
            );
            unset($u->device_token);

            return $u;
        });

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'with_device_only']),
        ]);
    }

    public function sendFcm(SendFcmRequest $request, User $user)
    {
        if (! filled($user->device_token)) {
            return back()->with('error', 'لا يوجد رمز جهاز مسجّل لهذا المستخدم.');
        }

        $data = $request->validated();

        // The image must live on a publicly-reachable host because FCM/APNs
        // fetch the URL from the internet when rendering the rich push. The
        // local `public` disk resolves to APP_URL (talabye.test in dev), which
        // those servers cannot reach — so store on the default disk (R2), the
        // same as every other image upload in the app. The resulting absolute
        // URL is what FcmBody carries into notification.image / apns.fcm_options.image.
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('fcm/images', config('filesystems.default'));
            $imageUrl = Storage::url($path);
        }

        try {
            app(Fcm::class)->send(new FcmBody([
                'token' => $user->device_token,
                'title' => $data['title'],
                'description' => $data['body'],
                'image' => $imageUrl,
                'url' => $data['url'] ?? null,
                'badge' => $user->notification_badges,
                'mutable_content' => $data['mutable_content'] ?? true,
            ]));
        } catch (\Throwable $e) {
            Log::error('Admin FCM send failed: '.$e->getMessage(), [
                'user_id' => $user->id,
            ]);

            return back()->with('error', 'تعذّر إرسال الإشعار. حاول مرة أخرى.');
        }

        return back()->with('success', 'تم إرسال الإشعار بنجاح.');
    }
}
