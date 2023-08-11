<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeacherSchoolTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers_schools', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('teacher_id')->default(0)->comment('教师id');
            $table->unsignedBigInteger('school_id')->default(0)->comment('学校id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['teacher_id', 'school_id'], 'teacher_school_index');
            $table->unique(['teacher_id', 'school_id'], 'teacher_school_unique');
            $table->comment = '教师学校关联表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teachers_schools');
    }
}
