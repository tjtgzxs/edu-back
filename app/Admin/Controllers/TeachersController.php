<?php

namespace App\Admin\Controllers;

use App\School;
use App\Teacher;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Pusher\Pusher;
use Encore\Admin\Facades\Admin as AdminUser;
class TeachersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Teacher';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        $grid = new Grid(new Teacher());
        $grid->setTitle('教师列表');
        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('email', __('Email'));
        $grid->column('status', __('Status'))->display(function ($status) {
            if($status==1) {
                return "<span style='color: green'>已经通过</span>";
            }else{
                return  "<button class='pass' data-id='$this->id'>审核</button>";
            }
        });
        $grid->column('role', __('Role'))->display(function ($role) {
            return Teacher::roleLabels[$role];
        });
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        // 添加发消息按钮
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
//        $grid->disableActions();
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
                        url: '/admin/teachers/pass/' + id, 
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
                url: '/admin/teachers/send', 
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
        return $grid;
    }

    protected function updateStatus($id){
//        $id = request('id');
        $teacher = Teacher::find($id);
        $teacher->status = !$teacher->status;
        $teacher->save();
        return redirect('/admin/teachers')->withSuccess('状态已更改');

    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Teacher::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('email', __('Email'));
        $show->field('role', __('Role'))->display(function ($role) {
            return Teacher::roleLabels[$role];
        });
        $show->field('status', __('Status'))->display(function ($status) {
            return Teacher::statusLabels[$status];
        });
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
        $form = new Form(new Teacher());

        $form->email('email', __('Email'))->required()->rules('required');
        $form->password('password', __('Password'))->required()->rules('required');
        $form->select('role',__('角色'))->options(
            [1=>'管理员', 2=>'普通老师']
        )->default(2)->rules("required|min:1");
        $form->hidden('name');
        $form->saving(function (Form $form){
            $form->name=$form->email;
        });
        $form->multipleSelect('schools',__('学校'))->required()->rules('required')->options(School::all()->pluck('name','id'));
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


    public function pass($id)
    {

        $teacher = Teacher::find($id);
        $teacher->status = 1;
        $teacher->save();
        return response()->json(['code' => 1, 'msg' => '已通过']);
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
