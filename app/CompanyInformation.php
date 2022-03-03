<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyInformation extends Model
{
    protected $table = 'company_information';

    protected $fillable = [
        'name', 'address', 'postal_code',
        'city', 'phone', 'fax', 'email',
        'web_site', 'capital', 'tax_number', 'bank',
        'iban', 'swift',
        'crc', 'crc_number'
    ];
}
