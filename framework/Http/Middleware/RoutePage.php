<?php

namespace CDeep\Http\Middleware;

use CDeep\Models\Page;
use Closure;
use Illuminate\Http\Request;

class RoutePage
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $action = $request->route()->getAction();
        if ($action && $action['page'] instanceof Page) {
            /**
             * @var Page $action['page']
             */
            $request->currentPage = $action['page']->setup();
        }
        return $next($request);
    }
}
