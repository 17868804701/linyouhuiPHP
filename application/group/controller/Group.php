<?php
/**
 * Created by PhpStorm.
 * User: UCT
 * Date: 2017/2/15
 * Time: 17:47
 */
namespace app\group\controller;
use app\admin\controller\Admin;

use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminListBuilder;
use app\admin\builder\AdminTreeListBuilder;

class Group extends Admin{
    function index(){
        //读取列表
        $map = array('status' => 1);
        $list = model('group')->where($map)->select();
        unset($li);
        $totalCount = model('group')->where($map)->count();

        $builder = new AdminListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';


        //显示页面
        $builder
            ->title('群组管理')
            ->buttonNew(url('group/Group/edit'))
            ->setStatusUrl(url('setStatus'))->button(lang('_DELETE_'),array('class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确实要删除吗？不可恢复，请谨慎删除！）', 'url' => url('del'), 'target-form' => 'ids'))
            ->keyId()->keyText('public_name', lang('_NAME_'))->keyText('wechat', '微信号')->keyText('public_id', '原始ID')->keyText('type',lang('_MPTYPE_'))->keyStatus()->keyDoActionEdit('edit?id=###')->keyDoAction('del?ids=###', lang('_DELETE_'))->keyDoAction('change?id=###', '切换')
            ->data($list)
            ->pagination($totalCount, 10);
        return $builder ->fetch();
    }

    /**
     * 删除
     * @param null $id
     * @author patrick<contact@uctoo.com>
     */
    public function del($ids = null){
        if (!$ids) {
            $this->error('请选择公众号');
        }
        $model = model('group');
        $res = $model->delete($ids);
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    public function edit($id = null)
    {

        if (request()->isPost()) {   //提交表单
            $model = model('group');                       //提交的时候用了model，model那些特性还是要用哒
            $data['group_name'] = input('post.group_name', '', 'op_t');
            $data['group_member'] = input('post.group_member', 0, 'op_t');
            $data['group_admin'] = input('post.group_admin', 1, 'op_t');
            $data['group_type'] = input('post.group_type', 1, 'op_t');
            $data['status'] = input('post.status', 0, 'intval');
            $data['sort'] = input('post.sort', 1, 'op_t');


            if ($id != 0) {
                $data['group_id'] = $id;
                $res = $model->isUpdate(true)->save($data);
            } else {
                $res = $model->isUpdate(false)->save($data);
            }
            $this->success(($id == 0 ? '添加' : '编辑') . '成功', $id == 0 ? url('', array('id' => $res)) : '');

        }else{   //显示表单
            $model = db('Group');  //显示的时候用了db，TODO::统一后台builder支持db和model，自动判断类型？
            if ($id != 0) {  //编辑
                $mp = $model->where(array('group_id' => $id))->find();
            } else{
                $mp = $model->select();
            }

            //显示页面
            $builder = new AdminConfigBuilder();
            $builder->title($id != 0 ? '编辑群组' : '添加群组')
                    ->keyId()->keyUid('group_id', '用户')->keyText
                    ('group_name', '名称')->keyText('group_member', '群组成员')->keyText('group_admin', '群组管理者')
                    ->keySelect('group_type', '类型', '请选择公众号类型', array(1,2,3))
                    ->keyEditor('group_name','编辑')
                    ->keyStatus()
                    ->data($mp)
                    ->buttonSubmit(url('edit'))->buttonBack();
                return  $builder->fetch();



        }

    }

}