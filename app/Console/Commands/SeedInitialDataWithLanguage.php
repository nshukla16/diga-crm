<?php

namespace App\Console\Commands;

use DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SeedInitialDataWithLanguage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:initial_with_language {language} {email?} {password?} {token?} {numberOfUsers?} {tariff?} {trialDays?} {days?} {modules?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed initial data with specified language and fill enabled modules with selected';

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
        $language = $this->argument('language');
        $email = $this->argument('email');
        $password = $this->argument('password');
        $token = $this->argument('token');
        $numberOfUsers = $this->argument('numberOfUsers');
        $tariff = $this->argument('tariff');
        $trialDays = $this->argument('trialDays');
        $days = $this->argument('days');
        $modules = $this->argument('modules');
        $seeder = new DatabaseSeeder;
        $seeder->run($language, $email, $password, $token, $numberOfUsers, $tariff, $trialDays, $days, $modules);
    }
}
