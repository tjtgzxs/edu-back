<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeRoleTeacherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('role_id');
            $table->tinyInteger('role')->default(1)->after('password')->comment('收否是管理员 1管理员 2普通老师');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teachers', function (Blueprint $table) {

                $table->dropColumn('role');
                $table->integer('role_id')->default(0)->after('password')->comment('角色id');
        });

    }
}
