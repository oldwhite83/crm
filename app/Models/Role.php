<?php

namespace App\Models;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole {
	protected $table = 'role';

	protected $fillable = ['name', 'pid', 'status', 'remark'];

	public $timestamps = false;

	public function roleUser(){
		return $this->hasMany('App\Models\roleUser','role_id','id');
	}

	public function users() {
		return $this->belongsToMany('App\Models\Admin', 'role_user', 'role_id', 'user_id');
	}

	public function perms() {
		return $this->belongsToMany('App\Models\Permission', 'permission_role', 'role_id', 'permission_id');
	}

	public function scopeActive($query) {
		return $query->where('status', 1);
	}

	public function usersCount() {
		return $this
		->users()
		->selectRaw('role_id, count(*) as aggregate')
		->where('is_delete',0);
	}
}
