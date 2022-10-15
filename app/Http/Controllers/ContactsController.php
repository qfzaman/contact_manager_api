<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactsController extends Controller
{
    public function store()
    {
        $data = request()->validate([
            'name' => 'required',
            'email' => 'required',
            'birthday' => '',
            'company' => '',
        ]);
        Contact::create($data);
    }
}
