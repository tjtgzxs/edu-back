<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Teacher extends Authenticatable implements JWTSubject
{
    protected $table = 'teachers';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'email', 'password','role','status'];
    use HasApiTokens,Notifiable;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function schools()
    {
        return $this->belongsToMany(School::class,'teachers_schools','teacher_id','school_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class,'teachers_students','teacher_id','student_id');
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    const statusLabels = [
        self::STATUS_INACTIVE => '未激活',
        self::STATUS_ACTIVE => '已激活',
    ];


    const ROLE_ADMIN = 1;
    const ROLE_TEACHER = 2;
    const  roleLabels=[
        self::ROLE_ADMIN => '管理员',
        self::ROLE_TEACHER => '普通老师',
    ];
}
