<?php

namespace App\Http\Controllers;

use App\Teacher;
use App\TeachersSchool;
use Illuminate\Http\Request;
use App\Student;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

class PusherController extends Controller
{


    public function authenticate(Request $request)
    {
        $socketId = $request->input('socket_id');
        $channelName = $request->input('channel_name');

        // 根据需求，验证用户是否有订阅该频道的权限

        $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'));
        $auth = $pusher->socket_auth($channelName, $socketId);

        return response($auth);
//        return response()->json(['message' => 'success', 'auth' => $auth]);
    }
}
