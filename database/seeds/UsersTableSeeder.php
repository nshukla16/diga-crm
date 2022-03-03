<?php

use App\Chat;
use App\Role;
use App\User;
use App\Setting;
use App\ChatMember;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($lang = 'ru', $email = 'admin@example.com', $password = 'password', $token = '')
    {
        DB::table('users')->insert([
            [
                'name' => 'admin',
                'email' => $email,
                'password' => bcrypt($password),
                'is_admin' => true,
                'photo' => '/img/no_profile_picture.png',
                'site_language' => $lang,
                'group_id' => 1
            ],
        ]);

        $setting1 = Setting::where('key', "external_token")->firstOrFail();
        $setting1->value = $token;
        $setting1->save();

        $admin = User::first();
        $admin->create_permissions();
        self::make_admin_roles($admin->id);

        $pins = User::pluck('pin')->toArray();
        while(true){
            $pin = rand(1000,9999);
            if (in_array($pin, $pins)){
                continue;
            }
            else{
                $admin->pin = $pin;                
                break;
            }
        }
        $admin->save();

        // // Add admin to common company's chat
        // $member = new ChatMember;
        // $member->user_id = $admin->id;
        // $member->chat_id = 1; // why it should by not 1?
        // $member->save();

        // // Add techsupport chat for admin
        // $chat = new Chat;
        // $chat->type = 3;
        // $chat->name = trans('template.Tech_support_chat');
        // $chat->save();

        // $member = new ChatMember;
        // $member->user_id = $admin->id;
        // $member->chat_id = $chat->id;
        // $member->save();

        // $member2 = new ChatMember;
        // $member2->user_id = 0;
        // $member2->chat_id = $chat->id;
        // $member2->save();
    }

    public function make_admin_roles($id)
    {
        foreach(User::get_role_models() as $action) {
            $role = Role::where(['user_id' => $id, 'action' => $action])->first();
            switch($role->action){
                case 'events':
                    $role->fill(['create' => 1, 'read' => 3, 'update' => 3, 'delete' => 3, 'addit' => 3]);
                    $role->save();
                    break;
                case 'services':
                case 'expences':
                case 'clients':
                case 'estimates':
                    $role->fill(['create' => 1, 'read' => 3, 'update' => 3, 'delete' => 3]);
                    $role->save();
                    break;
                case 'users':
                    $role->fill(['create' => 1, 'read' => 2, 'update' => 1, 'delete' => 0]);
                    $role->save();
                    break;
                case 'forks':
                    $role->fill(['create' => 1, 'read' => 0, 'update' => 0, 'delete' => 0]);
                    $role->save();
                    break;
                case 'plannings':
                    $role->fill(['create' => 0, 'read' => 1, 'update' => 1, 'delete' => 0]);
                    $role->save();
                    break;
                case 'projects':
                    $role->fill(['create' => 1, 'read' => 63, 'update' => 63, 'delete' => 0]);
                    $role->save();
                    break;
                case 'shipping_orders':
                    $role->fill(['create' => 1, 'read' => 1, 'update' => 1, 'delete' => 0]);
                    $role->save();
                    break;
                case 'legal_entities':
                    $role->fill(['create' => 1, 'read' => 1, 'update' => 1, 'delete' => 1]);
                    $role->save();
                    break;
                default:
                    $role->fill(['create' => 1, 'read' => 1, 'update' => 1, 'delete' => 1]);
                    $role->save();
            }
        }
    }
}