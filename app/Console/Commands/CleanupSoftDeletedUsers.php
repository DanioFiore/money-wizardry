<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Console\Command;

class CleanupSoftDeletedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete soft-deleted users older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Start to delete users...');
        
        $thirty_days_ago = Carbon::now()->subDays(30);
        
        $users = User::onlyTrashed()
            ->where('deleted_at', '<', $thirty_days_ago)
            ->get();
            
        $count = $users->count();
        
        if ($count === 0) {
            $this->info('No users to delete.');
            return Command::SUCCESS;
        }
        
        foreach ($users as $user) {
            $user->forceDelete();
        }
        
        $this->info("Delete {$count} users.");
        
        return Command::SUCCESS;
    }
}
