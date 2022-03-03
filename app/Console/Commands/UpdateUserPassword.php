<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class UpdateUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:change_password {email} {new_password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change password of specified user';

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
        $email = $this->argument('email');
        $new_password = $this->argument('new_password');
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->password = Hash::make($new_password);
            $user->save();
            $this->info('Done');
        }else{
            $this->alert('User not found');
        }
    }
}
