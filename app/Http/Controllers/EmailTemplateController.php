<?php

namespace App\Http\Controllers;

use App\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);
        return EmailTemplate::select($fields_array)->get();
    }
}
