<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    $router->get('users', 'UsersController@index');
    // 商品列表
    $router->get('products', 'ProductController@index');
    // 添加商品
    $router->get('products/create', 'ProductController@create');
    $router->post('products', 'ProductController@store');
    // 编辑商品
    $router->get('products/{id}/edit', 'ProductController@edit');
    $router->put('products/{id}', 'ProductController@update');
});
