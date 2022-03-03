<?php

namespace App\Console\Commands\OneTimeFixes;

use App\Site;
use Illuminate\Console\Command;
use Rkesa\Client\Models\ClientHistory;

class UpdateSites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'history:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'History sites migration';

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
        $sites = array('alpinism.rkas.pt' => 1,
                        'rkesa.pt' => 2,
                        'obras.rkesa.pt' => 3,
                        'isolamento.rkesa.pt' => 4,
                        'rkas.pt' => 5,
                        'alpinismo.rkas.pt' => 6,
                        'cozinhas.rkas.pt' => 7,
                        'remodelacao.rkas.pt' => 8,
                        'alpdecor.rkesa.pt' => 9,
                        'lp.rkesa.pt' => 10,
                        'metal.rkesa.pt' => 11,
                        'piquete.rkesa.pt' => 12,
                        'alpinismoindustrial.pt' => 13);
        foreach($sites as $key => $val){
            $s = new Site;
            $s->id = $val;
            $s->domain = $key;
            $s->save();
        }
        $assoc = array('alpinism.rkas.pt' => 5,
                        'rkesa.pt' => 7,
                        'obras.rkesa.pt' => 6,
                        'isolamento.rkesa.pt' => 8,
                        'rkas.pt' => 9,
                        'alpinismo.rkas.pt' => 10,
                        'cozinhas.rkas.pt' => 11,
                        'remodelacao.rkas.pt' => 12,
                        'alpdecor.rkesa.pt' => 13,
                        'lp.rkesa.pt' => 14,
                        'metal.rkesa.pt' => 15,
                        'piquete.rkesa.pt' => 16,
                        'alpinismoindustrial.pt' => 17);
        foreach ($assoc as $key => $val){
            $hs = ClientHistory::where('type_id', $val)->get();
            $s = Site::where('domain', $key)->first();
            foreach($hs as $h){
                $h->type_id = 5;
                $h->site_id = $s->id;
                $h->save();
            }
        }
        $this->info('Done');
    }
}
