<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(AddCartRequest $request)
    {
    	$user = $request->user();
    	$skuId = $request->input('sku_id');
    	$amount = $request->input('amount');

    	// 从数据库查询该商品是不是已经在购物车里
    	if ($cart = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
    		// 如果存在则叠加商品数量
    		$cart->update([
    			'amount' => $cart->amount + $amount,
    		]);
    	} else {
    		// 创建一个新的购物车记录
    		 $cart = new CartItem(['amount' => $amount]);
    		 $cart->user()->associate($user);
    		 $cart->productSku()->associate($skuId);
    		 $cart->save();
    	}

    	return [];
    }
}
