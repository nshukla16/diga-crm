<?php
namespace App\Http\Traits;

use Log;

trait ConnectAdditionalFieldsTrait {
    //a_attributes - значения (не все)
    //additional_fields - опции и названия (все)

    public function getAttributesCalculatedAttribute()
    {
        $arr = [];
        foreach($this->additional_fields as $field){
            $found = false;
            if ($this->a_attributes) { // can be null if we don't include it in ::select()
                foreach ($this->a_attributes as $attribute) {
                    if ($field['id'] == $attribute['id']) {
                        $found = true;
                        if ($attribute['type'] == 2) {
                            $v = self::get_option($field, $attribute['value']);
                            $arr[$attribute['id']] = array_merge(['value' => $attribute['value'], 'value_calculated' => $v], $field);
                        } else {
                            $arr[$attribute['id']] = array_merge(['value' => $attribute['value'], 'value_calculated' => $attribute['value']], $field);
                        }
                    }
                }
            }
            if (!$found){
                if ($field['type'] == 2){
                    $arr[$field['id']] = array_merge(['value' => $field['options'][0]['id'], 'value_calculated' => $field['options'][0]['name']], $field);
                }else{
                    $arr[$field['id']] = array_merge(['value' => '', 'value_calculated' => ''], $field);
                }
            }
        }
        return $arr;
    }

    private function get_option($field, $option_id)
    {
        $opt = '';
        foreach($field['options'] as $option) {
            if ($option['id'] == $option_id) {
                $opt = $option['name'];
                break;
            }
        }
        return $opt;
    }
}
