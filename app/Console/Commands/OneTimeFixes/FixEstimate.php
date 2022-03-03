<?php

namespace App\Console\Commands\OneTimeFixes;

use Illuminate\Console\Command;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Estimate\Models\EstimateLine;

class FixEstimate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'estimates:fix {estimate_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes broken lines';

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
        $estimate_id = $this->argument('estimate_id');
        $lines = EstimateLine::where('estimate_id', $estimate_id)->get();
        foreach($lines as $line){
            if (!is_null($line->parent_id) && is_null(EstimateLine::find($line->parent_id))){
                $line->delete();
            }
        }
        $this->info('Done');
    }
}
