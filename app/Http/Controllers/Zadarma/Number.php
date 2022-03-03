<?php

namespace App\Http\Controllers\Zadarma;

use App\User;
use App\Number as ZadarmaNumber;
use stdClass;

class Number {

    public function assignNumber($number, $user_id) {
        return User::where('id', $user_id)->update(['zadarma_internal_phonecode' => $number]);
    }

    public function unassignNumber($number, $user_id) {
        return User::where('id', $user_id)->update(['zadarma_internal_phonecode' => null]);
    }

    public function unassignAll() {
        return User::where('zadarma_internal_phonecode', '!=', null)
        ->update(['zadarma_internal_phonecode' => null]);
    }

    public function truncateNumbers() {
        $this->unassignAll();
        $nm = new ZadarmaNumber;
        return $nm::truncate();
    }

    public function insertNumbers() {

        $api = new Api();
        $c = $api->getCredentials();

        $this->truncateNumbers();

        foreach($c->numbers as $number) {
            $nm = new ZadarmaNumber;
            $nm->number = $number;
            $nm->internal = true;
            $nm->save();
        }

        $direct_numbers = $api->getDirectNumbers();

        foreach($direct_numbers as $d_number) {
            $zz = new ZadarmaNumber();
            $zz->number = (int) $d_number;
            $zz->internal = false;
            $zz->save();
        }

    }

    public function getAllNumbers() {
        $numbers = ZadarmaNumber::get();

        $out = new stdClass;
        $out->internal = array();
        $out->direct = array();


        foreach($numbers as $n) {
            if($n->internal)
                $out->internal[] = $n->number;
            else
                $out->direct[] = $n->number;
        }

        return $out;
    }

    public function getDirectNumbers() {
        return ZadarmaNumber::where('internal', '=', false)->get()->toArray();
    }

    public function getNumbersSimple() {
        return ZadarmaNumber::where('internal', '=', true)->get()->toArray();
    }

    public function getNumbers() {
        return ZadarmaNumber::with(['user'])->where('internal', '=', true)->get()->toArray();
    }

}
