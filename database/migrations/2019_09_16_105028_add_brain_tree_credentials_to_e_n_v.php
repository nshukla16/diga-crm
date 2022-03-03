<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBrainTreeCredentialsToENV extends Migration
{
    use \App\Http\Traits\DotenvTrait;

    public function up()
    {
        self::dotenv_add_line(base_path('.env'), "BRAINTREE_ENVIRONMENT=sandbox");
        self::dotenv_add_line(base_path('.env'), "BRAINTREE_MERCHANTID=m2hfqb6xghyp7rcs");
        self::dotenv_add_line(base_path('.env'), "BRAINTREE_PUBLIC_KEY=9mv2dmzymtmznh9c");
        self::dotenv_add_line(base_path('.env'), "BRAINTREE_PRIVATE_KEY=b293be589c4bc045d087998c315c5075");
        self::dotenv_reload(base_path());
    }

    public function down()
    {
        self::dotenv_remove_line_starting_with(base_path('.env'), 'BRAINTREE_ENVIRONMENT=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'BRAINTREE_MERCHANTID=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'BRAINTREE_PUBLIC_KEY=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'BRAINTREE_PRIVATE_KEY=');
    }
}
