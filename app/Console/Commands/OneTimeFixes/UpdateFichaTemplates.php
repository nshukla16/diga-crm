<?php

namespace App\Console\Commands\OneTimeFixes;

use Illuminate\Console\Command;
use Rkesa\Estimate\Models\EstimateLineFicha;

class UpdateFichaTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fichas:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update correction';

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
        $patterns = EstimateLineFicha::where('is_pattern', true)->get();
        foreach($patterns as $pattern){
            foreach($pattern->resources as $resource){
                switch($resource->resource_type){
                    case 0:
                    case 3:
                        $resource->correction = 1;
                        break;
                    case 1:
                    case 2:
                        $resource->correction = 0;
                        break;
                }
                $resource->save();
            }
            $pattern->quantity = 0;
            $pattern->save();
        }
        $this->info('Done');
    }
}
