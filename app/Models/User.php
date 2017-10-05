<?php

namespace App\Models;

class User extends \App\User
{

    use Api;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
	    'name',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
	    'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'email',
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $with = [
    ];

    /**
     * All of the dynamic fields to be added.
     *
     * @var array
     */
    protected $appends = [];

    protected $private = [];

}
