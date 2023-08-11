<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Teacher;
use App\Student;

class School extends Authenticatable
{
    protected $table = 'schools';
    protected $primaryKey = 'id';
    protected $fillable = ['name', ];
    use HasApiTokens,Notifiable;


    public function teachers()
    {
        return $this->belongsToMany(Teacher::class,'teachers_schools','school_id','teacher_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class,'school_id','id');
    }


}
