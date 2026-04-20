<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TutorController extends Controller
{
    public function index()
    {
        $docente = auth()->user()->docente;
        return view('tutor.index', compact('docente'));
    }
}
