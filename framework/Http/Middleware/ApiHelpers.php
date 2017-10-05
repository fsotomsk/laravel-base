<?php

namespace CDeep\Http\Middleware;

use Illuminate\Http\Request;

trait ApiHelpers {

    protected function isTrustedRequest(Request $request)
    {
        return ($request->server('REMOTE_ADDR') == '127.0.0.1');
    }

}