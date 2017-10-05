<?php

namespace CDeep\Models;


use CDeep\Helpers\DB\Model;

class Menu extends Model
{
    //
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'page_ids'  => 'array',
    ];

    public function getPagesAttribute()
    {
        return Page::whereIn('id', $this->page_ids)
            ->where('is_enabled',   1)
            ->where('is_published', 1)
            ->where('show_in_menu', 1)
            ->orderBy('menu_sort_order', 'asc')
            ->get();
    }

}
