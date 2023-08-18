<?php

namespace App\Http\Controllers;

use App\Student;
use App\Teacher;
use App\TeachersSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Pusher\Pusher;

class TeacherController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:teachers',
            'password' => 'required|string|min:6',
            'school_input' => 'required|array',
        ]);
        try {
            //开启事务
            \DB::beginTransaction();
            $teacher = Teacher::create([
                'name' => $request->email,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            foreach ($request->school_input as $school_name){
                //判断是否有重复的学校名称
                $school=\App\School::where('name',$school_name)->first();
                if(!$school){
                    $school=\App\School::create([
                        'name'=>$school_name,
                    ]);
                }
                //判断是否已经存在关联关系
                $teacher_school=\App\TeachersSchool::where('teacher_id',$teacher->id)->where('school_id',$school->id)->first();
                if(!$teacher_school){
                    $teacher->schools()->attach($school->id);
                }

            }

            //提交事务
            \DB::commit();
        }catch (\Exception $exception) {
            //回滚事务
            \DB::rollBack();
            return response()->json(['message' => 'system error','e-message'=>$exception->getMessage()], 401);
        }


     $token=Auth::guard('teachers')->attempt(['email'=>$request->email,'password'=>$request->password]);
     if($token){
         return response()->json(['token' => 'bearer '.$token], 201);
     }else{
            return response()->json(['message' => 'Invalid credentials'], 401);
     }

    }

    public function teacherAuth(Request $request){
        $teacher=Auth::guard('teachers')->user();
        return response()->json(['role'=>$teacher->role],200);
    }


    public function followStudent(){
        $teacher=Auth::guard('teachers')->user();
        $students=$teacher->students()->get();
        $result = array_map(function ($item){
            return ['name' => $item['name'], 'id' => $item['id'],'account'=>$item['account']];
        }, $students->toArray());
        return response()->json($result, 200);
    }


    public  function schools(){
        $teacher=Auth::guard('teachers')->user();
        $schools=$teacher->schools()->get();
        $result=[
            ['name'=>'select school','id'=>0]
        ];


        foreach ($schools->toArray() as $school){
            $result[]=['name'=>$school['name'],'id'=>$school['id']];
        }
        return response()->json($result, 200);
    }


    public function teachers(Request $request){
        //获取老师所在学校的所有老师
        $teacher_ids=TeachersSchool::where('school_id',$request->school_id)->pluck('teacher_id');
        $teachers=Teacher::whereIn('id',$teacher_ids)->where('status',1)->get();
        $result = array_map(function ($item){
            return ['name' => $item['name'], 'id' => $item['id'],'email'=>$item['email']];
        }, $teachers->toArray());
        return response()->json($result, 200);
    }

    public function students(Request $request){
        //获取老师所在学校的所有学生
        $students=Student::where('school_id',$request->school_id)->get();
        $result = array_map(function ($item){
            return ['name' => $item['name'], 'id' => $item['id'],'account'=>$item['account']];
        }, $students->toArray());
        return response()->json($result, 200);
    }

    public function createStudent(Request $request){
        $request->validate([
            'account' => 'required|string|unique:students',
            'password' => 'required|string|min:6',
            'school_id' => 'required|integer',
        ]);
        try {
            //开启事务
            \DB::beginTransaction();
            $student = Student::create([
                'name' => $request->account,
                'account' => $request->account,
                'password' => Hash::make($request->password),
                'school_id' => $request->school_id,
            ]);
            //提交事务
            \DB::commit();
        }catch (\Exception $exception) {
            //回滚事务
            \DB::rollBack();
            return response()->json(['message' => 'system error','e-message'=>$exception->getMessage()], 401);
        }
        return response()->json(['message' => 'success'], 200);
    }

    public function createTeacher(Request $request){
        $request->validate([
            'email' => 'required|email|unique:teachers',
            'password' => 'required|string|min:6',
            'school_id' => 'required|integer',
        ]);
        try {
            //开启事务
            \DB::beginTransaction();
            $teacher = Teacher::create([
                'name' => $request->email,
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->status,
                'password' => Hash::make($request->password),
            ]);
            $teacher->schools()->attach($request->school_id);
            //提交事务
            \DB::commit();
        }catch (\Exception $exception) {
            //回滚事务
            \DB::rollBack();
            return response()->json(['message' => 'system error','e-message'=>$exception->getMessage()], 401);
        }
        return response()->json(['message' => 'success'], 200);
    }


    public function sendMessage(Request $request)
    {
        $message = $request->message;
        $targetStudentId = $request->targetStudentId;
        $fromTeachertId = Auth::guard('teachers')->user()->id;
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
            'targetStudentId' => $targetStudentId,
            'fromTeacherId' => $fromTeachertId,
            'from_name'=>Auth::guard('teachers')->user()->name
        ]);

        return response()->json(['message' => 'Message sent successfully']);
    }





}
