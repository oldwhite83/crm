<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class UserInfo extends _BaseModel implements AuthenticatableContract
{
    use Authenticatable, EntrustUserTrait;

    protected $table = 'user_info';
    protected $primaryKey = 'uid';

    protected $fillable = ['uid', 'truename', 'sex', 'email', 'phone', 'qq', 'address', 'identity', 'college', 'specialty', 'graduation_year', 'company', 'job', 'employment_year', 'last_login_at', 'status', 'is_delete'];

    protected $hidden = ['remember_token'];

    public $incrementing = false;

    //用户->任务关系
    public function userTaskRelation()
    {
        return $this->hasMany('App\Models\UserTaskRelation', 'uid', 'uid')->where('is_delete', '=', '0');
    }

    //用户->班级关系
    public function classUserRelation()
    {
        return $this->hasMany('App\Models\ClassUserRelation', 'uid', 'uid')->where('is_delete', '=', '0');
    }

    //作业->用户作业关系
    public function userHomeworkRelation()
    {
        return $this->hasMany('App\Models\UserHomeworkRelation', 'uid', 'uid')->where('is_delete', '=', '0');
    }

    //备用
    public function userClasses()
    {
        //return $this->hasManyThrough('App\Models\ClassInfo', 'App\Models\ClassUserRelation', 'uid', 'uid');
    }

    //根据用户ID获取用户任务关系
    public function getUserTaskRelationById($uid)
    {
        return $this->find($uid)->userTaskRelation()->get(['*'])->toArray();
    }

    //根据用ID获取用户职业、路径、班级关系
    public function getClassUserRelationById($uid)
    {
        return $this->find($uid)->classUserRelation()->get(['*'])->toArray();
    }

    //根据用户ID获取用户、作业关系
    public function getHomeworkRelationById($uid)
    {
        return $this->find($uid)->userHomeworkRelation()->get(['*'])->toArray();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1)->where('is_delete', 0);
    }
}
