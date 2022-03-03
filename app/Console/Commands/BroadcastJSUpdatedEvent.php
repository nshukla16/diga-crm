<?php

namespace App\Console\Commands;

use App\Events\JSUpdated;
use Illuminate\Console\Command;

class BroadcastJSUpdatedEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:broadcast_front_changes';

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
        broadcast(new JSUpdated());
        $this->info('['.date('Y-m-d H:i:s').']'.env('APP_URL').': JavaScript changed event sent to all users');
    }
}
