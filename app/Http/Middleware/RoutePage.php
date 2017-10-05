<?php

namespace App\Http\Middleware;

use App\Models\Page;
use Closure;

class RoutePage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
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
