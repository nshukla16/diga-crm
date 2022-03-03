<?php

use App\Setting;
use App\Http\Traits\SaasAuthTrait;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\SaasPricesTrait;
use App\Module;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePrices extends Migration
{
    use SaasAuthTrait;
    use SaasPricesTrait;

    public function up()
    {
        if (env('APP_ENV') == 'production')
        {
            $object = self::get_from_saas(self::get_access_token());

            $price_per_user = $object['price_per_user'];
            $setting_price_per_user =  Setting::where('key', '=', 'price_per_user')->first();
            $setting_price_per_user->value = $price_per_user;
            $setting_price_per_user->save();
    
            foreach($object['modules_price_groups'] as $mpg)
            {
                $module = Module::where('name', '=', $mpg['module']['name'])->first();
                $module->price = $mpg['price'];
                $module->save();
            }
        }
    }

    public function down()
    {
    }
}
