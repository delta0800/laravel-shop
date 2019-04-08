<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAddressRequest;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class UserAddressesController extends Controller
{
    public function index(Request $request)
    {
    	return view('user_addresses.index', [
    		'addresses' => $request->user()->addresses,
    	]);
    }

    // 新增收货地址
    public function create()
    {
    	return view('user_addresses.create_and_edit', ['address' => new UserAddress()]);
    }

    // 修改收货地址
    public function store(UserAddressRequest $request)
    {
    	$request->user()->addresses()->create($request->only([
    		'province',
    		'city',
    		'district',
    		'address',
    		'zip',
    		'contact_name',
    		'contact_phone',
    	]));

    	return redirect()->route('user_addresses.index');
    }
}
