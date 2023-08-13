<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->put('teachers/{id}', 'TeachersController@updateStatus')->name('update.status');
    $router->resource('teachers', TeachersController::class);
    $router->get('teachers/pass/{id}', 'TeachersController@pass')->name('teachers.pass');
    $router->resource('students', StudentController::class);
    $router->post('teachers/send', 'TeachersController@sendMessage')->name('teachers.chat');

});
