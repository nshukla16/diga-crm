<?php

use App\Http\Traits\Auth0Trait;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class Auth0Seeder extends Seeder
{
    use Auth0Trait;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($email, $password)
    {
        $role_id = $this->create_role_and_urls();
        $this->create_user($email, $password, $role_id);
    }

    public function create_role_and_urls()
    {
        $management = self::get_management_token();
        $token = $management->access_token;

        $client = self::get_client($token, 'n2hhgGIZIjwoMxI8BOZeaU2bA2DJR4jM');
        array_push($client->callbacks, env('APP_URL') . '/auth0');
        array_push($client->allowed_logout_urls, env('APP_URL') . '/signoff');

        $client_to_update = new stdClass();
        $client_to_update->callbacks = $client->callbacks;
        $client_to_update->allowed_logout_urls = $client->allowed_logout_urls;

        $client_updated = self::patch_client_urls($token, 'n2hhgGIZIjwoMxI8BOZeaU2bA2DJR4jM', $client_to_update);

        $to_replace = array("http://", "https://", "www.");
        $role = str_replace($to_replace, "", env('APP_URL'));
        $created = self::post_role($token, $role);

        return $created->id;
    }

    public function create_user($email, $password, $role_id)
    {
        $user = User::first();

        $management = self::get_management_token();
        $token = $management->access_token;

        $auth0_users = self::user_exists($token, $user->email);
        if (count($auth0_users) > 0){
            $user->sub = $auth0_users[0]->user_id;
        }
        else {
            $auth0 = self::post_user($email, $password, "Administrator");
            $user->sub = 'auth0|' . $auth0->_id;
        }

        $role_add = self::add_role_to_user($user->sub, $role_id, $token);
        $user->save();
    }
}
