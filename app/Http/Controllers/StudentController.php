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

class StudentController extends Controller
{
    public function register(Request $request)
    {

        $request->validate([
            'account'=>'required|unique:students',
            'password'=>'required|string|min:6',
        ]);

        $student=Student::create([
            'name'=>$request->account,
            'account'=>$request->account,
            'password'=>Hash::make($request->password),
        ]);
        $token=Auth::guard('students')->attempt(['account'=>$request->account,'password'=>$request->password]);
        if($token){
            return response()->json(['token'=>'bearer '.$token],201);
        }else{
            return response()->json(['message'=>'Invalid credentials'],401);
        }

    }

    //老师列表
    public function list(Request $request)
    {
        $student=Auth::guard('students')->user();
        $teacher_ids=TeachersSchool::where('school_id',$student->school_id)->pluck('teacher_id');
        $teachers=Teacher::whereIn('id',$teacher_ids)->get();
        $student_teacher=$student->teachers()->get();
        $student_teacher_ids=Arr::pluck($student_teacher,'id');
        $result = array_map(function ($item) use ($student_teacher_ids){
            return ['name' => $item['name'], 'id' => $item['id'],'email'=>$item['email'],'followed'=>in_array($item['id'],$student_teacher_ids)];
        }, $teachers->toArray());

        return response()->json($result, 200);
    }

    public function follow(Request $request)
    {
        $request->validate([
            'teacher_id'=>'required|exists:teachers,id',
        ]);
        $student=Auth::guard('students')->user();
        //查看是否follow过
        $student_teacher=$student->teachers()->where('teacher_id',$request->teacher_id)->first();
        if($student_teacher){//如果follow过则解除follow
            $student->teachers()->detach($request->teacher_id);
            return response()->json(['followed'=>false],200);
        }
        $student->teachers()->attach($request->teacher_id);
        return response()->json(['followed'=>true],200);
    }


    public function sendMessage(Request $request)
    {
        $message = $request->message;
        $targetTeacherId = $request->targetTeacherId;
        $fromStudentId = Auth::guard('students')->user()->id;
        $options = [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'useTLS' => true
        ];

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $pusher->trigger('private-chat', 'client-message', [
            'message' => $message,
            'targetTeacherId' => $targetTeacherId,
            'fromStudentId' => $fromStudentId,
            'from_name'=>Auth::guard('students')->user()->name,
        ]);

        return response()->json(['message' => 'Message sent successfully']);
    }
}
