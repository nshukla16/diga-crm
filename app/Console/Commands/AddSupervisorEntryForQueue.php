<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddSupervisorEntryForQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:add_supervisor_queue_entry';

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
     * SUPERVISOR config is being removed in ERP-Saas ansible book
     *
     * @return mixed
     */
    public function handle()
    {
        $erp_instance_id = env('DB_DATABASE', 'instance');
        $file_path = '/etc/supervisor/conf.d/'.$erp_instance_id.'.conf';
        if (file_exists($file_path) === false) {
            $content = '[program:queue-'.$erp_instance_id.']
process_name=%(program_name)s
command=php '.base_path().'/artisan queue:work --sleep=3 --tries=1 --timeout=3600
autostart=true
autorestart=true
user=root
redirect_stderr=true
stdout_logfile='.base_path().'/storage/logs/worker.log';

            file_put_contents($file_path, $content, FILE_APPEND | LOCK_EX);
            $this->info('Supervisor entry for Queue added');
        }else{
            $this->info('Supervisor entry for Queue is already exist');
        }
    }
}
