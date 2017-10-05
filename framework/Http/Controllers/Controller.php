<?php

namespace CDeep\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var Request|null
     */
    protected $request = null;

    /**
     * @var bool
     */
    protected $isDebug = false;

    /**
     * Controller constructor.
     * @param Request|null $request
     */
    public function __construct(Request $request = null)
    {
        $this->isDebug = \App::environment('dev', 'testing', 'local');
        $this->request = $request ?: request();
    }

    /**
     * @return int|null
     */
    protected function getCurrentUserId()
    {
        try {
            return request()->user()->id;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param Request $request
     * @param $query
     * @param int $perPage
     * @return mixed
     */
    protected function paginate(Request $request, $query, $perPage=15)
    {
        return $query->paginate(
            min($request->get('per_page', max(1,$perPage)), 50)
        )
            ->appends(
                $request->query()
            );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|null
     */
    public function help(Request $request)
    {
        $action = $request->route()->getAction();
        $ns     = substr($action['class'], 0, strrpos($action['class'], '\\'));

        $action['routes_all'] = array_filter(
            array_map(function($classes) use ($ns){
                return array_filter($classes, function($route) use ($ns) {
                    return strpos($route['class'], $ns) !== false;
                });
            }, $action['routes_all']),
            function($classes){
                return sizeof($classes);
            }
        );

        return $this->isDebug
            ? view('api/help', $action)
            : null;
    }

}
