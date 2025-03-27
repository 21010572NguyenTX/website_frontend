<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminMiddleware
{
    /**
     * Kiểm tra xem người dùng đã đăng nhập và có vai trò admin hay không
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('user') || Session::get('user')['role'] !== 'admin') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập chức năng này'
                ], 403);
            }
            
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập trang này');
        }

        return $next($request);
    }
}