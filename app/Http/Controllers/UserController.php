<?php

namespace App\Http\Controllers;

class UserController extends Controller
{
    public function index()
    {
        return view('user.index');
    }

    public function edit($id)
    {
        return view('user.edit-user', compact('id'));
    }
}
