<?php

use App\MovementType;
use App\PaymentCondition;
use App\VatType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InvoiceDataSeed extends Migration
{
    public function up()
    {
        DB::table('vat_types')->insert([
            ['code' => 'PT_TNC', 'name' => 'Taxa normal Continente', 'percent' => 23],
            ['code' => 'PT_TNA', 'name' => 'Taxa normal Açores', 'percent' => 18],
            ['code' => 'PT_TNM', 'name' => 'Taxa normal Madeira', 'percent' => 22],
            ['code' => 'PT_TIC', 'name' => 'Taxa intermédia Continente', 'percent' => 13],
            ['code' => 'PT_TIA', 'name' => 'Taxa intermédia Açores', 'percent' => 9],
            ['code' => 'PT_TIM', 'name' => 'Taxa intermédia Madeira', 'percent' => 12],
            ['code' => 'PT_TRC', 'name' => 'Taxa reduzida Continente', 'percent' => 6],
            ['code' => 'PT_TRA', 'name' => 'Taxa reduzida Açores', 'percent' => 4],
            ['code' => 'PT_TRM', 'name' => 'Taxa reduzida Madeira', 'percent' => 5],
        ]);

        DB::table('movement_types')->insert([
            ['name' => 'TRFB', 'description' => 'Receber transferência bancária', 'days' => 0],
            ['name' => 'DEP', 'description' => 'Receber por cheque', 'days' => 3]
        ]);

        DB::table('payment_conditions')->insert([
            ['name' => 'Pronto pagamento', 'days' => 0],
            ['name' => 'Fatura a 90 dias', 'days' => 90]
        ]);
    }

    public function down()
    {
        VatType::all()->delete();
        PaymentCondition::all()->delete();
        MovementType::all()->delete();
    }
}
