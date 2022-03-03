<?php

namespace App\Console\Commands\OneTimeFixes;

use Illuminate\Console\Command;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Service\Models\Service;

class UpdateServicePrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update price for services taking into account master estimate';

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
        $services = Service::whereNotNull('master_estimate_id')->get();
        foreach($services as $service){
            $estimate = Estimate::find($service->master_estimate_id);
            $service->estimate_summ = self::global_price($estimate);
            $service->save();
        }
        $this->info('Done');
    }

    private function global_price($estimate){
        if($estimate->discount){
            $price_with_addit_and_disc = $estimate->price*(1+$estimate->additional_price/100) - $estimate->price * (1+$estimate->additional_price/100) * ($estimate->discount / 100);
        }else{
            $price_with_addit_and_disc = $estimate->price*(1+$estimate->additional_price/100);
        }
        return round($price_with_addit_and_disc, 2);
    }
}
