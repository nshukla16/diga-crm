<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Estimate\Models\EstimateLine;

class CreateCorrectLineableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estimate_lines', function (Blueprint $table) {
            $table->integer('correct_lineable_id');
            $table->string('correct_lineable_type');
        });
        $lines = EstimateLine::all();
        foreach($lines as $line) {
            $line->correct_lineable_id = $line->lineable_id;
            switch($line->lineable_type){
                case '\App\EstimateLineCategory':
                    $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineCategory';
                    break;
                case '\App\EstimateLineData':
                    $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineData';
                    break;
                case '\App\EstimateLineFicha':
                    $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineFicha';
                    break;
                case '\App\EstimateLineSeparator':
                    $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineSeparator';
                    break;
            }
            $line->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('estimate_lines', function (Blueprint $table) {
            $table->dropColumn(['correct_lineable_id', 'correct_lineable_type']);
        });
    }
}
