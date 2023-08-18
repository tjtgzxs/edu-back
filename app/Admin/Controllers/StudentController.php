<?php

namespace App\Admin\Controllers;

use App\School;
use App\Student;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin as AdminUser;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Pusher\Pusher;

class StudentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Student';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Student());
        $grid->setTitle('学生列表');
        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('account', __('Account'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('school_id',__("学校"))->display(
            function (){
                if($this->school_id==0){
                    return "未分配学校";
                }
                return $this->school->name;
            }
        );
        /**
         * 创建模态框
         */
        $this->script = <<<EOT



        $('.pass').unbind('click').click(function() {
            var id = $(this).data('id');
            swal({
                title: "确认同意该用户的申请吗？",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "确认",
                showLoaderOnConfirm: true,
                cancelButtonText: "取消",
                preConfirm: function() {
                    $.ajax({
                        method: 'get',
                        url: '/admin/student/pass/' + id, 
                        success: function (data) {
                            console.log(data);
                            $.pjax.reload('#pjax-container');
                            if(data.code){
                                swal(data.msg, '', 'success');
                            }else{
                                swal(data.msg, '', 'error');
                            }
                        }
                    });
                }
            });
        });
        $('.send').unbind('click').click(function() {
            var student_id = $(this).data('id');
            message=prompt("请输入消息");
            data={
                'message':message,
                'student_id':student_id,
                '_token': LA.token
                
            };
            $.ajax({
                method: 'post',
                url: '/admin/students/send', 
                data:data,
                success: function (data) {
                    console.log(data);
                   
                    if(data.code){
                        swal(data.msg, '', 'success');
                    }else{
                        swal(data.msg, '', 'error');
                    }
                }
            });
        });
EOT;
        Admin::script($this->script);
        $grid->column('发消息')->display(function () {

            return  "<button class='send' data-id='$this->id'>发消息</button>";
        });
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            // 去掉编辑
            $actions->disableEdit();

            // 去掉查看
            $actions->disableView();

        });

//        $grid->disableCreateButton();
        $grid->disableRowSelector();
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Student::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('account', __('Account'));
        $show->field('password', __('Password'));
        $show->field('school_id', __('School id'));
        $show->field('remember_token', __('Remember token'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Student());

        $form->text('account', __('Account'))->creationRules(['required',"unique:students,account"]);
        $form->password('password', __('Password'));
        $form->hidden('name');
        $form->saving(function (Form $form){
            $form->password=Hash::make($form->password);
            $form->name=$form->account;
        });
        $form->select('school_id',__('学校'))->required()->rules('required')->options(School::all()->pluck('name','id'));
        $form->footer(function ($footer){
            $footer->disableViewCheck();

            // disable `Continue editing` checkbox
            $footer->disableEditingCheck();

            // disable `Continue Creating` checkbox
            $footer->disableCreatingCheck();
        });
        $form->isCreating();
        $form->confirm('确定提交吗？');
        return $form;
    }


    public function sendMessage(Request $request)
    {
        $message = $request->message;
        $targetStudentId = $request->student_id;
        $fromManagerId =AdminUser::user()->id;
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
            'fromManagerId' => $fromManagerId
        ]);

        return response()->json(['msg' => 'Message sent successfully','code'=>1]);
    }
}
