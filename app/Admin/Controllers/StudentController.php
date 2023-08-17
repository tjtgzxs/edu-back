<?php

namespace App\Admin\Controllers;

use App\School;
use App\Student;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;

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

        $form->text('account', __('Account'));
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
}
