<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('user_id')) {
            return redirect('/');
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $user = \App\Models\User::where('email', $request->email)
            ->where('password', $request->password)
            ->first();

        if (!$user) {
            return back()->with('error', 'Sai tài khoản hoặc mật khẩu');
        }

        session(['user_id' => $user->id]);

        return redirect('/');
    }

    public function logout()
    {
        session()->forget('user_id');
        return redirect('/login');
    }

    public function store_user(Request $request)
    {
        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'company_id' => $request->company_id
        ]);

        return back()->with('success', 'Thêm user thành công');
    }
    public function update_user(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'company_id' => $request->company_id,
        ]);

        // nếu nhập password thì mới update
        if ($request->password) {
            $user->update([
                'password' => $request->password
            ]);
        }

        return back()->with('success', 'Cập nhật thành công');
    }

    public function delete_user($id)
    {
        User::findOrFail($id)->delete();

        return back()->with('success', 'Xóa thành công');
    }
}
