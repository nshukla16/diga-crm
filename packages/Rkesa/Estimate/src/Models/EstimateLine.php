<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;
use Log;

class EstimateLine extends Model
{
    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function parent()
    {
        return $this->belongsTo(EstimateLine::class);
    }

    public function lineable()
    {
        return $this->morphTo(); // not working! because of wrong lineable_type (legacy)
    }

    public function correct_lineable()
    {
        return $this->morphTo();
    }

    public function estimate_line_workers()
    {
        return $this->hasMany(EstimateLineWorker::class)->with('user');
    }

    public static function array_to_tree_and_to_array($arr){
        $root = array('id' => -1, 'parent_id' => 0);
        $arr = $arr->toArray();
        array_unshift($arr, $root);
        foreach ($arr as &$a){
            if (is_null($a['parent_id'])){
                $a['parent_id'] = -1;
            }
        }
        $tree = self::array_to_tree($arr);
        $array = self::tree_to_array($tree);
        array_shift($array);
        foreach ($array as &$a){
            $a['childs'] = '';
            if ($a['parent_id'] == -1){
                $a['parent_id'] = null;
            }
        }
        return $array;
    }

    public static function array_to_tree($items) {
        $childs = array();

        foreach($items as &$item) $childs[$item['parent_id']][] = &$item;
        unset($item);

        foreach($items as &$item) if (isset($childs[$item['id']]))
            $item['childs'] = $childs[$item['id']];

        return $childs[0];
    }

    public static function tree_to_array($array) {
        $result = array();
        foreach($array as $key => $row) {
            $result[] = $row;
            if(array_key_exists('childs', $row) && count($row['childs']) > 0) {
                $result = array_merge($result, self::tree_to_array($row['childs']));
            }
        }
        return $result;
    }

    public static function gen_number($lineid, $data){
        $my = null;
        $line_index = 0;
        if (!is_array($data[0])) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = $data[$i]->attributes;
            }
        }
        foreach ($data as $line){
            $line_index++;
            if ($line['id']==$lineid) {
                $my = $line;
                break;
            }
        }
        $result = 0;
        for ($i = $line_index-1; $i>=0; $i--) {
            if ($data[$i]['parent_id']===$my['parent_id'] && $data[$i]['lineable_type']!=='\\App\\EstimateLineSeparator')
                $result++;
        }
        if (!is_null($my['parent_id'])) {
            $result = self::gen_number($my['parent_id'], $data).'.'.$result;
        }
        return $result;
    }
}
