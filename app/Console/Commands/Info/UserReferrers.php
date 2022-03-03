<?php

namespace App\Console\Commands\Info;

use Illuminate\Console\Command;
use Rkesa\Client\Models\Client;

class UserReferrers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //php artisan user:referrers '2017-09-18 00:00:00' '2017-10-17 00:00:00'
    protected $signature = 'user:referrers {first_date} {second_date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Referrers by day from interval';

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
        $first_date = $this->argument('first_date');
        $second_date = $this->argument('second_date');
        $clients = Client::whereBetween('created_at', [$first_date, $second_date])->orderBy('created_at')->groupBy('created_at', 'client_referrer_id')->get();
        foreach($clients as $client){
            if ($client->client_referrer_id) {
                $ref = $client->client_referrer->title;
                $ref = $ref.str_repeat(" ", 40-strlen($ref));
                $this->info($client->created_at . ' '.$ref . trim($client->main_contact()->surname.' '.$client->main_contact()->name));
            }
        }
        $this->info('Done');
    }
}
