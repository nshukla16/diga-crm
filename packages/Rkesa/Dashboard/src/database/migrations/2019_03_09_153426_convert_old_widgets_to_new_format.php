<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Dashboard\Models\DashboardWidget;

class ConvertOldWidgetsToNewFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $widgets = DashboardWidget::all();
        foreach ($widgets as $widget){
            if ($widget->widget_type == 3 && $widget->data_type == 1){
                $widget->data_type = 1;
                $widget->save();
            }elseif ($widget->widget_type == 3 && $widget->data_type == 2){
                $widget->data_type = 2;
                $widget->save();
            }elseif ($widget->widget_type == 4){
                $widget->data_type = 7;
                $widget->save();
            }elseif ($widget->widget_type == 1 && $widget->data_type == 1){
                $widget->data_type = 3;
                $widget->save();
            }elseif ($widget->widget_type == 1 && $widget->data_type == 2){
                $widget->data_type = 4;
                $widget->save();
            }elseif ($widget->widget_type == 2 && $widget->data_type == 1){
                $widget->data_type = 5;
                $widget->save();
            }elseif ($widget->widget_type == 2 && $widget->data_type == 2){
                $widget->data_type = 6;
                $widget->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $widgets = DashboardWidget::all();
        foreach ($widgets as $widget){
            switch($widget->data_type){
                case 1:
                    $widget->widget_type = 3;
                    $widget->data_type = 1;
                    break;
                case 2:
                    $widget->widget_type = 3;
                    $widget->data_type = 2;
                    break;
                case 3:
                    $widget->widget_type = 1;
                    $widget->data_type = 1;
                    break;
                case 4:
                    $widget->widget_type = 1;
                    $widget->data_type = 2;
                    break;
                case 5:
                    $widget->widget_type = 2;
                    $widget->data_type = 1;
                    break;
                case 6:
                    $widget->widget_type = 2;
                    $widget->data_type = 2;
                    break;
                case 7:
                    $widget->widget_type = 4;
                    $widget->data_type = null;
                    break;
            }
            $widget->save();
        }
    }
}
