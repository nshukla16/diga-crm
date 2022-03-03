<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BalanceHistory extends Model
{
    protected $table = 'balance_history';

    protected $fillable = [
        'transfer_amount',
        'amount_before',
        'amount_after',
        'title',
    ];
}
