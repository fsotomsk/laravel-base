<?php

namespace CDeep\Http\Controllers\Api;

use CDeep\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class Main
 * @package App\Http\Controllers\Api
 */
class Main extends Controller
{

    /**
     * @param Request $request
     * @return mixed
     *
     * @route  /
     *
     * Справка
     */
    public function help(Request $request)
    {
        return parent::help($request);
    }

    /**
     * @param Request $request
     * @return mixed
     *
     * @route  /get_token
     */
    public function token(Request $request)
    {
        return [
            'token' => str_random(60),
        ];
    }

    /**
     * Otherwise /*
     */
    public function error404(Request $request)
    {
        return abort(404);
    }
}
