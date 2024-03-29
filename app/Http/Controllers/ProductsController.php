<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index (Request $request)
    {
    	// 创建一个查询构造器
    	$builder = Product::query()->where('on_sale', true);
    	// 判断有没有提交 search 参数，如果有就赋值给 $search 变量
    	// search 参数用来模糊搜索商品
    	if ($search = $request->input('search', '')) {
    		$like = '%'.$search.'%';
    		// 模糊搜索商品标题、商品详情、SKU 标题、SKU 描述
    		$builder->where(function ($query) use ($like) {
    			$query->where('title', 'like', $like)
    				->orWhere('description', 'like', $like)
    				->orWhereHas('skus', function ($query) use ($like) {
    					$query->where('title', 'like', $like)
    						->orWhere('description', 'like', $like);
    				});
    		});
    	}

    	// 有没有提交 order 参数，如果有就赋值给 $order 变量
    	// order 参数用来控制商品的排序规则
    	if ($order = $request->input('order', '')) {
    		// 有没有以 _asc 或者 _desc 结尾
    		if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
    			// 如果字符串的开头是这 3 个字符串之一，就说明是一个合法的排序值
    			if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
    				// 根据传入的排序值来构造排序参数
    				$builder->orderBy($m[1], $m[2]);
    			}
    		}
    	}

    	$products = $builder->paginate(16);

    	return view('products.index', [
    		'products' => $products,
    		'filters' => [
    			'search' => $search,
    			'order' => $order,
    		],
    	]);
    }

    // 商品详情
    public function show(Product $product, Request $request)
    {
    	// 判断商品有没有上架，如果没有上架则抛出异常
    	if (!$product->on_sale) {
    		throw new InvalidRequestException('商品没有上架');
    	}

    	$favored = false;
    	// 用户没有登录时返回的是 null，已登录时返回的是对应的用户对象
    	if ($user = $request->user()) {
    		// 从当前用户已收藏的商品搜索 id 为当前商品 id 的商品
    		// boolval() 函数用于把值转为布尔值
    		$favored = boolval($user->favoriteProducts()->find($product->id));
    	}

    	return view('products.show', ['product' => $product, 'favored' => $favored]);
    }

    // 商品收藏
    public function favor(Product $product, Request $request)
    {
    	$user = $request->user();
    	if ($user->favoriteProducts()->find($product->id)) {
    		return [];
    	}

    	$user->favoriteProducts()->attach($product);

    	return [];
    }

    // 取消收藏
    public function disfavor(Product $product, Request $request)
    {
    	$user = $request->user();
    	$user->favoriteProducts()->detach($product);

    	return [];
    }

    // 收藏列表
    public function favorites(Request $request)
    {
    	$products = $request->user()->favoriteProducts()->paginate(16);

    	return view('products.favorites', ['products' => $products]);
    }
}
