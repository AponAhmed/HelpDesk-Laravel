<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Cron\GetMailController;

class MailGetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute the index method of GetMailController';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $controller = new GetMailController();
        $controller->index();
        $this->info('Executed GetMailController@index successfully.');
    }
}
