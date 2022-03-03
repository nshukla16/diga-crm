<?php

use Illuminate\Database\Seeder;
use Rkesa\Client\Seeds\AruSeeder;
use Illuminate\Support\Facades\Log;
use Rkesa\Estimate\Seeds\UnitsSeeder;
use Rkesa\Client\Seeds\ReferrersSeeder;
use Rkesa\Calendar\Seeds\EventTypesSeeder;
use Rkesa\Dashboard\Seeds\DashboardSeeder;
use Rkesa\Project\Seeds\ProjectTypesSeeder;
use Rkesa\Service\Seeds\ServiceTypesSeeder;
use Rkesa\Project\Seeds\LegalEntitiesSeeder;
use Rkesa\Service\Seeds\ServiceScopesSeeder;
use Rkesa\Service\Seeds\ServiceStatesSeeder;
use Rkesa\Project\Seeds\ProjectStatusesSeeder;
use Rkesa\Service\Seeds\ServicePrioritiesSeeder;
use Rkesa\CalendarExtended\Seeds\EventGroupsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($lang = 'ru', $email = 'admin@example.com', $password = 'password', $token = '', $numberOfUsers = 0, $tariff = 'base', $trialDays = 7, $days = 0, $modules = '')
    {
        $this->call(UsersTableSeeder::class, $lang, $email, $password, $token);
        $this->call(GroupSeeder::class, $lang);
        $this->call(GlobalSettingsSeeder::class, $lang);
        $this->call(EventTypesSeeder::class, $lang);
        $this->call(ReferrersSeeder::class, $lang);
        $this->call(ServiceStatesSeeder::class, $lang);
        $this->call(ServicePrioritiesSeeder::class, $lang);
        $this->call(ServiceTypesSeeder::class, $lang);
        $this->call(UnitsSeeder::class, $lang);
        $this->call(AruSeeder::class);
        $this->call(DashboardSeeder::class, $lang);
        $this->call(ClientSeeder::class, $lang);
        $this->call(EventGroupsSeeder::class, $lang);
        $this->call(ServiceScopesSeeder::class, $lang);
        $this->call(ProjectTypesSeeder::class, $lang);
        $this->call(ProjectStatusesSeeder::class, $lang);
        $this->call(LegalEntitiesSeeder::class, $lang);
        $this->call(TariffAndModulesSeeder::class, $lang, $email, $password, $token, $numberOfUsers, $tariff, $trialDays, $days, $modules);
        $this->call(Auth0Seeder::class, $email, $password);
    }

    // Passing variable to seeder
    public function call($class, $extra = null, $email = 'admin@example.com', $password = 'password', $token = '', $numberOfUsers = 0, $tariff = 'base', $trialDays = 7, $days = 0, $modules = '')
    {
        if (isset($this->command)) {
            $this->command->getOutput()->writeln("<info>Seeding:</info> $class");
        }

        $this->resolve($class)->run($extra, $email, $password, $token, $numberOfUsers, $tariff, $trialDays, $days, $modules);
    }
}
