<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckWhatsAppNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-whats-app-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for appointments needing reminders or auto-cancellation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // This command is deprecated as we moved to Microservices.
        // Keeping it empty to prevent scheduler errors.
        \Illuminate\Support\Facades\Log::info("CheckWhatsAppNotifications command executed (Empty for Microservice).");
        return 0;
    }
}
