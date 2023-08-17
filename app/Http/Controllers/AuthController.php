<?php

namespace App\Http\Controllers;

use App\Teacher;
use App\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'account' => 'string',
                'password' => 'required|string',
                'role' => 'required|in:teachers,students', // 确保 role 是 'teacher' 或 'student'
            ]);
            $guard = $request->role == 'teachers' ? 'teachers' : 'students';
            if ($guard == 'teachers') {

                $token=Auth::guard($guard)->attempt(['email'=>$request->account,'password'=>$request->password]);

                if ($token) {
                    $teacher = Teacher::where('email', $request->account)->first();
                    if ($teacher->status == 0)
                        return response()->json(['message' => 'Please wait activation'], 402);
                }
            } else {
                $token=Auth::guard($guard)->attempt(['account'=>$request->account,'password'=>$request->password]);

            }
            if($token) {
                return response()->json(['token' => 'bearer '.$token], 201);
            }else{
                return response()->json(['message' => 'password error'], 401);
            }
        }catch (\Exception $exception) {
            return response()->json(['message' => 'Invalid credentials','e-message'=>$exception->getMessage()], 401);
        }




    }
    public function redirectToLine()
    {
        return Socialite::driver('line')->redirect();
    }

    public function handleLineCallback()
    {
        $user = Socialite::driver('line')->user();

        // 处理用户信息并进行登录
    }
}
