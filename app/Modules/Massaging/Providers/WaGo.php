<?php

namespace App\Modules\Massaging\Providers;

use App\Modules\Massaging\Contracts\MessageSender;

class WaGo implements MessageSender
{
    public function send($to, $message, $countryCode = '967'): bool
    {
        $client = new \GuzzleHttp\Client();
        $client->post(sprintf('%s/send/message', config('services.massaging.providers.wa-go.url')), [
            'headers' => [
                'X-Device-Id' => $this->getRandomDeviceId(),
            ],
            'auth' => [
                config('services.massaging.providers.wa-go.username'),
                config('services.massaging.providers.wa-go.password')
            ],
            'form_params' => [
                'phone' => $countryCode . $to,
                'message' => $this->getMessageTemplate($message),
            ],
        ]);

        return true;
    }

    private function getRandomDeviceId(): string
    {
        $devices = config('services.massaging.providers.wa-go.deviceIds');
        $devices = explode(',', $devices);
        if (empty($devices)) {
            $devices = [config('services.massaging.providers.wa-go.deviceId')];
        }

        return $devices[array_rand($devices)];
    }

    private function getMessageTemplate($otp): string
    {
        $messages = [
            "تطبيق طلبي\nرمز التحقق الخاص بك هو: :otp\nلا تشارك هذا الرمز مع أي شخص.",
            "أهلاً بك في طلبي!\nكود التفعيل: :otp\nاستمتع بالتسوق!",
            "تطبيق طلبي\nكود تفعيل حسابك هو: :otp\nصالح لمدة 5 دقائق.",
            "حفاظاً على أمان حسابك في طلبي\nرمز الدخول لمرة واحدة هو: :otp",
            "جوعان؟ قمت بخطوة واحدة فقط!\nرمز التحقق لتطبيق طلبي هو: :otp",
            "طلبك ينتظرك!\nأدخل الرمز :otp في تطبيق طلبي لتأكيد هويتك.",
            "عزيزي العميل،\nرمز التحقق (OTP) الخاص بـ طلبي هو: :otp",
            "رمز التحقق لتطبيق طلبي هو :otp\nتنبيه: لا تشارك الكود مع أحد.",
            "رمز التحقق السريع من طلبي:\n:otp",
            "اشتقنا لك في طلبي!\nرمز الدخول الخاص بك هو: :otp",
            "تطبيق طلبي\nرمز التحقق: :otp",
            "كود تفعيل حسابك في طلبي\nالرمز: :otp",
            "استخدم الرمز :otp\nلإكمال دخولك إلى تطبيق طلبي",
            "تطبيق طلبي\nرمز التأكيد (OTP): :otp",
            "إليك رمز الدخول الخاص بك في طلبي:\n:otp",
            "تأكيد الهوية - طلبي\nالرمز هو: :otp",
            "رمز التحقق لمرة واحدة\n:otp\nتطبيق طلبي",
            "أدخل الكود :otp\nلتأكيد طلبك في تطبيق طلبي",
            "كود التحقق من طلبي: :otp\nشكراً لاستخدامك تطبيقنا.",
            "رمز تسجيل الدخول لـ طلبي\nالكود: :otp"
        ];
        return str_replace(":otp", $otp, $messages[array_rand($messages)]);
    }
}
