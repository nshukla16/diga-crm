<?php

namespace App\Console\Commands\OneTimeFixes;

use Illuminate\Console\Command;
use Rkesa\Project\Models\ManufacturerOrder;

class FixTransportationTotal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:transportation_total';

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
        $orders = ManufacturerOrder::all();
        foreach($orders as $order){
            $total = 0;
            foreach($order->transportation_payments()->get() as $payment){
                $total += $payment->in_main_currency;
            }
            $order->transportation_total = $total;
            $order->save();
        }
    }
}
