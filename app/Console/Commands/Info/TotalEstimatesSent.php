<?php

namespace App\Console\Commands\Info;

use Illuminate\Console\Command;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Service\Models\Service;

class TotalEstimatesSent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //php artisan estimates:total_sent '2017-08-01 00:00:00' '2017-09-01 00:00:00'
    protected $signature = 'estimates:total_sent {first_date} {second_date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get total estimates sent count and total price';

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
        $history = ClientHistory::where(array('type_id' => 2, 'service_state_id' => 8))->whereBetween('created_at', [$first_date, $second_date])->groupBy('service_id')->get();
        $sum = 0;
        foreach ($history as $entity){
            $sum += Service::find($entity->service_id)->estimate_summ;
        }
        $this->info('Done: '. 'Total price - '.$sum.' Total count - '.count($history));
    }
}
