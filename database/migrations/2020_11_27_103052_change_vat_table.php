<?php

use App\VatType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeVatTable extends Migration
{
    public function up()
    {
        Schema::table('vat_types', function (Blueprint $table) {
            $table->string('country_region')->nullable();    
        });

        $vat_types = VatType::all();
        foreach($vat_types as $vat_type){
            switch($vat_type->name){
                case 'Taxa normal Continente':
                    $vat_type->country_region = 'PT';
                    $vat_type->code = 'NOR';
                break;
                case 'Taxa normal Açores':
                    $vat_type->country_region = 'PT-AC';
                    $vat_type->code = 'NOR';
                break;
                case 'Taxa normal Madeira':
                    $vat_type->country_region = 'PT-MA';
                    $vat_type->code = 'NOR';
                break;
                case 'Taxa intermédia Continente':
                    $vat_type->country_region = 'PT';
                    $vat_type->code = 'INT';
                break;
                case 'Taxa intermédia Madeira':
                    $vat_type->country_region = 'PT-MA';
                    $vat_type->code = 'INT';
                break;
                case 'Taxa intermédia Açores':
                    $vat_type->country_region = 'PT-AC';
                    $vat_type->code = 'INT';
                break;
                case 'Taxa reduzida Continente':
                    $vat_type->country_region = 'PT';
                    $vat_type->code = 'RED';
                break;
                case 'Taxa reduzida Açores':
                    $vat_type->country_region = 'PT-AC';
                    $vat_type->code = 'RED';
                break;
                case 'Taxa reduzida Madeira':
                    $vat_type->country_region = 'PT-MA';
                    $vat_type->code = 'RED';
                break;

                case 'Regime de isenção de IVA':
                    $vat_type->country_region = 'PT';
                    $vat_type->code = 'ISE';
                break;
            }
            $vat_type->save();
        }

        DB::table('vat_types')->insert([
            ['country_region' => 'PT-AC', 'code' => 'ISE', 'name' => 'Regime de isenção de IVA Açores', 'percent' => 0],
            ['country_region' => 'PT-MA', 'code' => 'ISE', 'name' => 'Regime de isenção de IVA Madeira', 'percent' => 0]
        ]);
    }

    public function down()
    {
        Schema::table('vat_types', function (Blueprint $table) {
            $table->dropColumn('country_region');
        });
    }
}
