<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeacherStudentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers_students', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('teacher_id')->default(0)->comment('教师id');
            $table->unsignedBigInteger('student_id')->default(0)->comment('学生id');
            $table->timestamps();
            $table->index(['teacher_id', 'student_id'], 'teacher_student_index');
            $table->unique(['teacher_id', 'student_id'], 'teacher_student_unique');
            $table->comment = '学生对老师关注表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teachers_students');
    }
}
