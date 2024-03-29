<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSku;
use App\Models\UserAddress;
use Carbon\Carbon;

class OrdersController extends Controller
{
    public function store(OrderRequest $request)
    {
    	$user = $request->user();
    	// 开启一个数据库事务
    	$order = \DB::transaction(function () use ($user, $request) {
    		$address = UserAddress::find($request->input('address_id'));
    		// 更新此地址的最后使用时间
    		$address->update(['last_used_at', Carbon::now()]);
    		// 创建订单
    		$order = new Order([
    			// 将地址信息放入订单里
    			'address' => [
    				'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
    			],
    			'remark' => $request->input('remark'),
    			'total_amount' => 0,
    		]);
    		// 订单关联到当前用户
    		$order->user()->associate($user);
    		// 写入数据库
    		$order->save();

    		$totalAmount = 0;
    		$items = $request->input('items');
    		// 遍历用户提交的 SKU
    		foreach ($items as $data) {
    			$sku = ProductSku::find($data['sku_id']);
    			// 创建一个 OrderItem 并直接与当前订单关联
    			$item = new OrderItem();
    			$item->amount = $data['amount'];
    			$item->price = $sku->price;
    			$item->order()->associate($order);
    			$item->product()->associate($sku->product_id);
    			$item->productSku()->associate($sku);
    			$item->save();
    			$totalAmount += $sku->price * $data['amount'];
    			if ($sku->decreaseStock($data['amount']) <= 0) {
    				throw new InvalidRequestException('该商品库存不足');
    			}
    		}

    		// 更新订单总金额
    		$order->update(['total_amount' => $totalAmount]);

    		// 将下单的商品从购物车里移除
    		$skuIds = collect($items)->pluck('sku_id');
    		$user->cartItems()->whereIn('product_sku_id', $skuIds)->delete();

    		return $order;
    	});

    	return $order;
    }
}
