<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
//use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Student extends  Authenticatable implements JWTSubject
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'account', 'password','school_id'];
    use HasApiTokens,Notifiable;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function school()
    {
        return $this->belongsTo(School::class,'school_id','id');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class,'teachers_students','student_id','teacher_id');
    }

}
