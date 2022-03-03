<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddCronEntryForThisProject extends Command
{
    protected $signature = 'project:add_cron_entry';

    protected $description = 'Adds cron entry if it does not exist';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $file_path = '/var/spool/cron/crontabs/root';

        $entry = '* * * * * cd '.base_path().' && php artisan schedule:run >> /dev/null 2>&1';

        $file_content = file_get_contents($file_path);
        if (strpos($file_content, $entry) === false) 
        {
            file_put_contents($file_path, $entry.PHP_EOL, FILE_APPEND | LOCK_EX);
            $this->info('Incron entry added');
        }
        else
        {
            $this->info('Incron entry is already exist');
        }
    }
}
