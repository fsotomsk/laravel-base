<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class Main extends Controller
{

    public function routes_js()
    {
        return \Cache::remember('api:v1:routes_js', 10, function() {

            $pages = $this->index();

            $tplCache   = ['var templateCacheSet=function($templateCache){'];
            $routeCache = ['var routeProviderSet=function($routeProvider){$routeProvider'];
            $linksCache = ['var linksProviderSet=function(){return'];
            $pagesCache = ['var pagesProviderSet = function(){ return '];

            $pagesLinksCache = [];
            $pagesSiteCache = [];
            foreach ($pages as $page) {

                $p = [
                    'id'            => $page->id,
                    'page_id'       => $page->page_id,
                    'uri'           => $page->uri,
                    'back_page_id'  => $page->back_page_id,
                    'templateUrl'   => '/views/' . $page->template_view . '.html',
                    'link'          => $page->angular_link,
                    'title'         => $page->title,
                    'topic'         => $page->topic,
                    'menu'          => $page->menu,
                    'tabs'          => $page->tabs,
                    'path'          => array_map(function($v){ return preg_replace('#([a-zA-Z0-9_-]+):#ui', ':', $v); }, $page->path),
                    'show_in_menu'  => $page->show_in_menu,
                    'top_menu_type' => $page->top_menu_type,
                    'is_marked'         => $page->is_marked,
                    'child_ids'         => $page->child_ids,
                    //'marked_child_ids'  => $page->marked_child_ids,
                ];

                $pagesLinksCache[ $p['link'] ] = $p['id'];
                $pagesSiteCache[ $p['id'] ] = $p;

                $source = view()->exists($page->template_view) ? view($page->template_view) : '';
                $tplCache[]   = '$templateCache.put("' . $p['templateUrl'] . '", "' . str_replace(["\n", "\r"], ['\n', ''], addslashes($source)) . '");';
                $routeCache[] = '.when("' . $p['link'] . '", ' . json_encode($p) . ')';
            }

            $tplCache[]   = '};';
            $routeCache[] = '.otherwise(' . json_encode($pagesSiteCache[4]) . ');};';
            $pagesCache[] = json_encode($pagesSiteCache) . ';};';
            $linksCache[] = json_encode($pagesLinksCache) . ';};';

            return  '/* ts:' . time() . ' */'
            . ' ' . implode('', $tplCache)
            . ' ' . implode('', $routeCache)
            . ' ' . implode('', $linksCache)
            . ' ' . implode('', $pagesCache);
        });
    }

	/**
     * Display a version
     *
     * @return \Illuminate\Http\Response
     */
    public function version()
    {
        //
        return app()->version();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return \Cache::remember('api:v1:pages', 9, function() {
            return Page::where('is_enabled', 1)
                ->where('is_published', 1)
                ->get();
        });
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return abort(401, 'Access denied');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        return abort(401, 'Access denied');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function view(Request $request)
    {
        $page = $request->currentPage;

        $params = [];
        $params['Page'] = $page;

        $params['_CONTENT'] = view()->exists($page->view)
            ? view($page->view, $params)
            : null;

        return view()->exists($page->env)
            ? view($page->env, $params)
            : $params['_CONTENT'];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Page
     */
    public function show($id)
    {
        return Page::where('is_enabled', 1)
            ->where('is_published', 1)
            ->where('id', $id)
            ->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        return abort(401, 'Access denied');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        return abort(401, 'Access denied');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        return abort(401, 'Access denied');
    }

    /**
     * @return null
     */
    public function noop()
    {
        return null;
    }
}
