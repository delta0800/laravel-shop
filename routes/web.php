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

Route::get('/', 'PagesController@root')->name('root');

Auth::routes();

// 邮箱验证
Route::group(['middleware' => 'auth'], function() {
	Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');
	Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');
	Route::get('/email_verification/verify', 'EmailVerificationController@verify')->name('email_verification.verify');
	// 测试
	Route::group(['middleware' => 'email_verified'], function() {
		// 收货地址
		Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
	});
});
