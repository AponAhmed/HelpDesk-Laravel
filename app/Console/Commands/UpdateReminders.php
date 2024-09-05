<?php

namespace App\Console\Commands;

use App\Models\MailList;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update records where reminder date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $now = Carbon::now();
        MailList::whereNotNull('reminder')
            ->where('reminder', '<=', $now)
            ->update([
                'reminder' => null,
                'labels' => DB::raw("CONCAT(labels, ',UNREAD')"),
                'created_at' => $now, // Set created_at to the current time
                'updated_at' => $now  // Set updated_at to the current time
            ]);

        $this->info('Reminders updated successfully.');
    }
}
