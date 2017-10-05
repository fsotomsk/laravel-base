<?php

namespace CDeep\Models;


use CDeep\Helpers\DB\Model;

class Group extends Model
{

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
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
	];

	/**
	 * The attributes that should be visible for arrays.
	 *
	 * @var array
	 */
	protected $visible = [
		'id',
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
	protected $appends = [
	];

	protected $private = [
	];

}
