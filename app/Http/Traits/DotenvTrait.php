<?php
namespace App\Http\Traits;

use Dotenv\Dotenv;

trait DotenvTrait {

    private function dotenv_add_line($file, $str)
    {
        file_put_contents($file, PHP_EOL.$str.PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function dotenv_remove_line_starting_with($file, $str)
    {
        $string = file_get_contents($file);
        $array = explode("\n", $string);
        $output = array();
        foreach($array as $arr) {
            if(!(preg_match('/^'.$str.'[^\r\n]*/m', $arr))) {
                $output[] = $arr;
            }
        }
        $out = implode("\n", $output);
        file_put_contents($file, $out);
    }

    /*
     *  Renames key (only)
     */
    private function dotenv_change_line($file, $old_name, $new_name)
    {
        $string = file_get_contents($file);
        $array = explode("\n", $string);
        $output = array();
        foreach($array as $arr) {
            if((preg_match('/^'.$old_name.'[^\r\n]*/m', $arr))) {
                $output[] = $new_name.'='.substr($arr, strpos($arr, '=')+1);
            }else {
                $output[] = $arr;
            }
        }
        $out = implode("\n", $output);
        file_put_contents($file, $out);
    }

    private function dotenv_change_value($file, $key, $value)
    {
        $string = file_get_contents($file);
        $array = explode("\n", $string);
        $output = array();
        foreach($array as $arr) {
            if((preg_match('/^'.$key.'[^\r\n]*/m', $arr))) {
                $output[] = $key.'='.$value;
            }else {
                $output[] = $arr;
            }
        }
        $out = implode("\n", $output);
        file_put_contents($file, $out);
    }

    private function dotenv_reload($path)
    {
        $dotenv = new Dotenv($path);
        $dotenv->load();
    }
}
