<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Cron\SendMailController;

class MailSendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature =  'mail:send';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute the send method of SendMailController';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $controller = new SendMailController();
        $controller->send();
        $this->info('Executed SendMailController@send successfully.');
    }
}
