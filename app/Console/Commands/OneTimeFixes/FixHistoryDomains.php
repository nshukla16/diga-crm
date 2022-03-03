<?php

namespace App\Console\Commands\OneTimeFixes;

use App\Site;
use App\User;
use Log;
use Illuminate\Console\Command;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientHistory;

class FixHistoryDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'history:fix_site_id';

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
        $cls = ClientHistory::where('type_id', 5)->get();
        foreach($cls as $cl){
            $client = Client::find($cl->client_id);
            if ($client->client_referrer){
                $domain = $client->client_referrer->title;
                $cl->site_id = Site::where('domain', $domain)->first()->id;
                $cl->save();
            }else{
                $this->error($client->id);
            }
        }
        $this->info('Done');
    }
}
