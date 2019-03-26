<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Cache;
use Exception;
use Illuminate\Http\Request;
use Mail;

class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
    	// 从 url 中取出 `email` 和 `token` 两个参数
    	$email = $request->input('email');
    	$token = $request->input('token');

    	// 如果有一个为空说明不是一个合法的验证链接，直接抛出异常
    	if (!$email || !$token) {
    		throw new Exception('验证链接不正确');
    	}

    	// 从缓存中读取数据，我们把从 url 中获取的 `token` 与缓存中的值做对比
    	// 如果缓存不存在或者返回值与 url 中的 `token` 不一致就抛出异常
    	if ($token != Cache::get('email_verification_'.$email)) {
    		throw new Exception('验证链接不正确或已过期');
    	}

    	// 根据邮箱从数据库中获取对应的用户
    	// 通常来说能通过 token 校验的情况下不可能出现用户不存在
    	// 但是为了代码的健壮性我们还是需要做这个判断
    	if (!$user = User::where('email', $email)->first()) {
    		throw new Exception(‘用户不存在);
    	}

    	// 将制定的 key 删除
    	Cache::forget('email_verification_'.$email);

    	// 修改 `email_verified` 为 `true`
    	$user->update(['email_verified' => true]);

    	return view('pages.success', ['msg' => '邮箱验证成功']);
    }

    // 手动发送邮箱验证
    public function send(Request $request)
    {
    	$user = $request->user();

    	// 判断用户有没有邮箱验证
    	if ($user->email_verified) {
    		throw new Exception('你已经验证过邮箱了');
    	}

    	// 调用 notify() 发送邮箱验证
    	$user->notify(new EmailVerificationNotification());

    	return view('pages.success', ['msg' => '邮件发送成功']);
    }
}
