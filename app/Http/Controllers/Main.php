<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Main extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     *
     * @route  /
     *
     * Welcome page
     */
    public function welcome(Request $request)
    {
        return view('welcome');
    }
}
