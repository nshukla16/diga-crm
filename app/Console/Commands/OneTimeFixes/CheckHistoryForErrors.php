<?php

namespace App\Console\Commands\OneTimeFixes;

use Illuminate\Console\Command;
use Rkesa\Client\Models\ClientHistory;

class CheckHistoryForErrors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'history:check_errors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $history_data = ClientHistory::all();
        foreach($history_data as $history){
            if (strpos($history->message, '&quot;') !== false) {
                $this->info("&quot; - ".$history->id);
            }
            if (strpos($history->message, '&#10;') !== false) {
                $this->info("&#10; - ".$history->id);
            }
        }
        $this->info('Done');
    }
}
