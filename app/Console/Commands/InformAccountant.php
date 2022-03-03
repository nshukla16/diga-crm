<?php

namespace App\Console\Commands;

use App\Call;
use App\Notifications\NewMissedCall;
use App\Setting;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Estimate\Models\EstimatePayStage;
use Rkesa\Planning\Notifications\PaymentStep;

class InformAccountant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inform:accountant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Accountant will receive a message ';

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
        $today = Carbon::now()->format('Y-m-d 00:00:00');
        $payStages = EstimatePayStage::where('payment_date', 'LIKE', $today)->first();
        $accountant_id = Setting::find(9)->value;
        if($payStages !== null){
            $estimate_id = $payStages->estimate_id;
            $estimate = Estimate::find($estimate_id);
            $estimate_number = $estimate->get_estimate_number();
            User::find($accountant_id)->notify(new PaymentStep($estimate_number, $estimate_id));
        }
    }
}
