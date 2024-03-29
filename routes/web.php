<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', '/products')->name('root');
Route::get('products', 'ProductsController@index')->name('products.index');

Auth::routes();

// 邮箱验证
Route::group(['middleware' => 'auth'], function() {
	Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');
	Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');
	Route::get('/email_verification/verify', 'EmailVerificationController@verify')->name('email_verification.verify');
	// 测试
	Route::group(['middleware' => ['auth', 'email_verified']], function() {
		// 收货地址
		Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
		// 新增收货地址
		Route::get('user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
		Route::post('user_addresses', 'UserAddressesController@store')->name('user_addresses.store');
		// 修改收货地址
		Route::get('user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
		// 更新收货地址
		Route::put('user_addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
		// 删除收货地址
		Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');
		// 商品收藏
		Route::post('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
		// 取消收藏
		Route::delete('products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
		// 收藏列表
		Route::get('products/favorites', 'ProductsController@favorites')->name('products.favorites');
		// 加入购物车
		Route::post('cart', 'CartController@add')->name('cart.add');
		// 查看购物车
		Route::get('cart', 'CartController@index')->name('cart.index');
		// 购物车删除商品
		Route::delete('cart/{sku}', 'CartController@remove')->name('cart.remove');
		// 创建订单
		Route::post('orders', 'OrdersController@store')->name('orders.store');
	});
});

// 商品详情
Route::get('products/{product}', 'ProductsController@show')->name('products.show');
