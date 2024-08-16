<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocenteCOntroller extends Controller
{
    public function showDocente(Request $request)
    {
        $user = $request->userData;
        return view('docentes.index', compact('user'));
    }
}
