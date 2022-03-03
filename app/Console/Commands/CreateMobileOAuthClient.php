<?php

namespace App\Console\Commands;

use App\Http\Controllers\AuthController;
use Illuminate\Console\Command;
use Laravel\Passport\ClientRepository;

class CreateMobileOAuthClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oauth:ensure_mobile_client_exist';

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
        if (\Laravel\Passport\Client::where('name', AuthController::MOBILE_CLIENT)->count() == 0) {
            $clients = new ClientRepository();
            $clients->create(null, AuthController::MOBILE_CLIENT, '', false, true);
            $this->info('Created');
        }else{
            $this->info('Already exist');
        }
    }
}
