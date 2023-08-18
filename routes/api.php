<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('cors')->group(function () {
    Route::post('login', 'AuthController@login');
    Route::prefix('teachers')->group(function () {
        Route::post('register', 'TeacherController@register');

        // 添加其他老师相关的 API 路由
    });

    Route::prefix('students')->group(function () {
        Route::post('register', 'StudentController@register');
        Route::post('chat', 'StudentController@sendMessage');

    });
    Route::post('login', 'AuthController@login');
    Route::post('pusher/auth', 'PusherController@authenticate');

    Route::middleware('api.refresh')->group(function () {
        Route::prefix('students')->group(function () {
            Route::get('teacher_list', 'StudentController@list');
            Route::put('teacher_follow', 'StudentController@follow');
        });
        Route::prefix('teachers')->group(function () {
            Route::get('teacher_auth', 'TeacherController@teacherAuth');
            Route::get('followers', 'TeacherController@followStudent');
            Route::get('schools', 'TeacherController@schools');
            Route::get('teachers', 'TeacherController@teachers');
            Route::get('students', 'TeacherController@students');
            Route::post('create_teacher', 'TeacherController@createTeacher');
            Route::post('create_student', 'TeacherController@createStudent');
            Route::post('chat', 'TeacherController@sendMessage');
        });


    });

});




