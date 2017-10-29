<?php

namespace CDeep\Models;


use CDeep\Helpers\DB\Model;
use Illuminate\View\FileViewFinder;

class Page extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'description',
        'keywords',
        'is_published',
        'menu_sort_order',
        'is_enabled',
        'created_at',
        'deleted_at',
        'updated_at',
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $with = [
        //'child',
    ];

    /**
     * All of the dynamic fields to be added.
     *
     * @var array
     */
    protected $appends = [
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'show_in_menu'  => 'boolean',
        'is_enabled'    => 'boolean',
        'is_published'  => 'boolean'
    ];

	/**
	 * @var \Illuminate\Database\Eloquent\Collection|null
	 */
	protected static $_pagesCache = null;

    /**
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection|mixed|null
     */
	public static function indexed($id=null)
	{
	    try {
            if (self::$_pagesCache === null) {
                self::$_pagesCache = \Cache::remember('app:pages:indexed', 1, function() {
                    return parent::all()->keyBy('id') ?: [];
                });
            }
            if ($id !== null) {
                return self::$_pagesCache->get($id) ?: null;
            }
        } catch (\Exception $e) {
	        return $id ? null : [];
        }
		return self::$_pagesCache ?: [];
	}

    public function isReadable($user)
    {
        if (!$user) {

        }
        return false;
    }

    public function isWritable($user)
    {
        return false;
    }

	/**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form()
    {
        return $this->hasOne('App\Models\PageForm', 'page_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function child()
    {
        return $this->hasMany('App\Models\Page', 'page_id', 'id');
    }

    /**
     * @return array|null
     */
    public function getParentAttribute()
    {
        if ($this->page_id) {
            return self::indexed($this->page_id);
        }
        return null;
    }

    /**
     * @var array
     */
    protected $_path        = [];

    /**
     * @param $parent
     * @return array
     */
    protected function getPathAttributeIterator($parent)
    {
        if ($parent) {
            if ($parent->parent) {
                $this->getPathAttributeIterator($parent->parent);
            }
            $this->_path[$parent->id] = ($parent->uri == 'index')
                ? null
                : $parent->uri;
        }
        return $this->_path;
    }

    /**
     * @return array
     */
    public function getPathAttribute()
    {
        if ($this->_path) {
            return $this->_path;
        }

        $this->_path            = $this->getPathAttributeIterator($this->parent);
	    $this->_path[$this->id] = ($this->uri == 'index')
            ? null
            : $this->uri;

        return $this->_path;
    }

    /**
     * @return string
     */
    public function getLinkAttribute()
    {
        $path = $this->getPathAttribute();
        return  implode('/', $path)
		        . ($path
		            ? ($this->child()->count() ? '/' : '.html')
		            : null);
    }

    /**
     * @return mixed
     */
    public function getHttpLinkAttribute()
    {
        return preg_replace('#/([a-zA-Z0-9_-]+):([a-zA-Z0-9_-]+)(/|.html)#ui', '/{\\2}\\3', $this->link);
    }

    /**
     * @return mixed
     */
    public function getAngularLinkAttribute()
    {
        return preg_replace('#/([a-zA-Z0-9_-]+):#ui', '/:', $this->link);
        //return preg_replace('#/([a-zA-Z0-9_-]+):([a-zA-Z0-9_-]+)(/|.html)#ui', '/:\\2\\3', $this->link);
    }

    /**
     * @return mixed|null
     */
    public function getResourceAttribute()
    {
        if(!$this->template_resource) {
            return $this->parent
                ? $this->parent->getResourceAttribute()
                : null;
        }
        return $this->template_resource;
    }

    /**
     * @return mixed|null
     */
    public function getEnvAttribute()
    {
        if(!$this->template_env) {
            return $this->parent
                ? $this->parent->getEnvAttribute()
                : null;
        }
        return $this->template_env;
    }

    /**
     * @return mixed
     */
    public function getViewAttribute()
    {
        return $this->template_view;
    }

    /**
     * @return mixed
     */
    public function getIsCurrentAttribute()
    {
        $current = request()->currentPage;
        if ($current) {
            return ($this->id === $current->id);
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getSubMenuAttribute()
    {
        return $this->child()
            ->where('is_enabled',   1)
            ->where('is_published', 1)
            ->where('show_in_menu', 1)
            ->orderBy('menu_sort_order', 'asc')
            ->get();
    }

    public function setup()
    {
        $paths = [
            resource_path($this->resource . DIRECTORY_SEPARATOR . 'views'),
            resource_path('default/views'),
            implode(DIRECTORY_SEPARATOR, [__DIR__ , '..', '..', 'resources', $this->resource, 'views']),
            implode(DIRECTORY_SEPARATOR, [__DIR__ , '..', '..', 'resources', 'default', 'views']),
        ];
        app('view')->setFinder(new FileViewFinder(app()['files'], $paths));

        return $this;
    }

}
