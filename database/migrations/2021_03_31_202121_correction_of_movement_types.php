<?php

use App\Invoice;
use App\MovementType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CorrectionOfMovementTypes extends Migration
{
    public function up()
    {
        $movement_types = MovementType::all();
        foreach ($movement_types as $movement_type) {
            Invoice::where('movement_type_id', $movement_type->id)->delete();
        }

        MovementType::query()->delete();

        DB::table('movement_types')->insert([
            ['name' => 'TB', 'description' => 'Transferencia bancaria ou debito direto autorizado', 'days' => 0],
            ['name' => 'CC', 'description' => 'Cartao credito', 'days' => 0],
            ['name' => 'CD', 'description' => 'Cartao debito', 'days' => 0],
            ['name' => 'CH', 'description' => 'Cheque bancario', 'days' => 0],
            ['name' => 'CI', 'description' => 'Credito documentario internacional', 'days' => 0],
            ['name' => 'CO', 'description' => 'Cheque ou cartao oferta', 'days' => 0],
            ['name' => 'CS', 'description' => 'Compensacao de saldos em conta corrente', 'days' => 0],
            ['name' => 'DE', 'description' => 'Dinheiro eletronico, por exemplo em cartoes de fidelidade ou de pontos', 'days' => 0],
            ['name' => 'LC', 'description' => 'Letra comercial', 'days' => 0],
            ['name' => 'NU', 'description' => 'Numerario', 'days' => 0],
            ['name' => 'OU', 'description' => 'Outros meios aqui nao assinalados', 'days' => 0],
            ['name' => 'PR', 'description' => 'Permuta de bens', 'days' => 0],
            ['name' => 'TR', 'description' => 'titulos de compensacao extrassalarial independentemente do seu suporte', 'days' => 0],
        ]);
    }

    public function down()
    {
    }
}
