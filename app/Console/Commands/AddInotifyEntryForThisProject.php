<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddInotifyEntryForThisProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:add_incrontab_entry';

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
        $incron_file_path = '/var/spool/incron/root';
        $entry = base_path().'/public/js IN_MODIFY /bin/bash '.base_path().'/incron_script_after_js_changed.sh';
        $incron_file_content = file_get_contents($incron_file_path);
        if (strpos($incron_file_content, $entry) === false) {
            file_put_contents($incron_file_path, $entry.PHP_EOL, FILE_APPEND | LOCK_EX);
            $this->info('Incron entry added');
        }else{
            $this->info('Incron entry is already exist');
        }
    }
}
