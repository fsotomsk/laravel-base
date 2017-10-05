<?php

namespace CDeep\Helpers\DB;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as CModel;

/**
 * App\Framework\Model
 *
 * @mixin \Eloquent
 * @property int $current_user_id
 */
class Model extends CModel
{
    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'updated_at';

    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();
    }

    /**
     * @param array $with
     * @return $this
     */
    public function setWith($with)
    {
        $this->with = $with;
        return $this;
    }
}