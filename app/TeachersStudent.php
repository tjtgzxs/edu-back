<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Teacher;

class TeachersStudent extends Authenticatable
{
    protected $table = 'teachers_students';
    protected $primaryKey = 'id';
    protected $fillable = ['teacher_id', 'student_id'];
    use HasApiTokens,Notifiable;





}
