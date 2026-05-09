<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PurgeTrashedUsersCommand extends Command
{
    protected $signature = 'users:purge-trashed {--days=30}';

    protected $description = 'Permanently delete users that have been in trash longer than the given days (default 30)';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $users = User::onlyTrashed()
            ->where('deleted_at', '<=', $cutoff)
            ->get();

        if ($users->isEmpty()) {
            $this->info("No trashed users older than {$days} days.");
            return self::SUCCESS;
        }

        foreach ($users as $user) {
            $user->tokens()->delete();
            $user->forceDelete();
        }

        $this->info("Purged {$users->count()} user(s) trashed before {$cutoff->toDateTimeString()}.");

        return self::SUCCESS;
    }
}
