<?php
// +----------------------------------------------------------------------
// | UCToo [ Universal Convergence Technology ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2015 http://uctoo.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Patrick <contact@uctoo.com>
// +----------------------------------------------------------------------


namespace app\admin\controller;

use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminListBuilder;
/**
 * 微信模板消息配置控制器
 * @author patrick <contact@uctoo.com>
 */
class Tplmsg extends Admin
{

    /**
     * 模板列表
     * @author patrick <contact@uctoo.com>
     */
    public function index($page=1,$r=20)
    {
        //读取数据
        $model = db('Tplmsg');
        $kmap['uid'] = $map['uid'] = UID;
        $map['public_id'] = get_token();
        $mp =  db('MemberPublic')->where($map)->find();  //所登陆会员帐号当前管理的公众号
        $kmap['mp_id'] = $mp['mp_id'];
        $list = $model->where($kmap)->page($page, $r)->order('id asc')->select();
        $totalCount = $model->where($kmap)->count();
        //显示页面
        $builder = new AdminListBuilder();
        return $builder
            ->title('模板消息列表')
            ->buttonNew(url('Tplmsg/edit'))->button('删除',array('class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确实要删除模板消息吗？（不可恢复，请谨慎删除！）', 'url' => url('del'), 'target-form' => 'ids'))
            ->keyId()->keyText('template_id', '消息模板ID')->keyText('name', '标题')->keyText('industry', '行业')->keyDoAction('Tplmsg/field?id=###', '管理字段')->keyDoActionEdit('edit?id=###')->keyDoAction('del?ids=###', '删除')
            ->data($list)
            ->pagination($totalCount, $r)
            ->fetch();
    }

    /**
     * 删除消息模板
     * @param null $id
     * @author patrick<contact@uctoo.com>
     */
    public function del($ids = null){
        if (!$ids) {
            $this->error('请选择消息模板');
        }
        $model =  db('Tplmsg');
        $res = $model->delete($ids);
        if ($res) {
            $model =  db('Tplmsg_field');
            $map['pid']  = array('in',$ids);
            $res = $model->where($map)->delete();  //删除模板下的字段
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    public function edit($id = 0, $uid = 0,$mp_id = 0,$template_id = '',$name = '',$industry = '',$topcolor = '#FFFFFF',$content = '',$url = '')
    {
        $model = db('Tplmsg');
        if (request()->isPost()) {   //提交表单
            $data['uid'] = $uid;
            $data['mp_id'] = $mp_id;
            $data['template_id'] = $template_id;
            if ($data['template_id'] == '') {
                $this->error('模板ID不能为空！');
            }
            $data['name'] = $name;
            $data['industry'] = $industry;
            $data['topcolor'] = $topcolor;
            $data['content'] = $content;
            $data['url'] = $url;

            if ($id != 0) {
                $res = db('Tplmsg')->where('id=' . $id)->save($data);
            } else {
                $res = db('Tplmsg')->add($data);
            }
            if ($res) {
                $msg = $id == '' ? "添加模板消息成功" : "编辑模板消息成功";
                return ['status'=>true,'info'=>$msg.'11'];
            } else {
                $msg = $id == '' ? "添加模板消息失败" : "编辑模板消息失败";
                return ['status'=>true,'info'=>$msg.'12'];
            }

        }else{   //显示表单
            //读取模板消息信息
            if ($id != 0) {  //编辑
                $tplmsg = $model->where(array('id' => $id))->find();
            } else {  //新增
                $tplmsg = array('uid' => UID,'mp_id' => get_mpid(),'topcolor' => '#FFFFFF');
            }

            //显示页面
            $builder = new AdminConfigBuilder();
            return $builder->title($id != 0 ? '编辑模板消息' : '添加模板消息')
                    ->keyId()->keyHidden('uid','')->keyHidden('mp_id','')->keyText('template_id', '消息模板ID', '消息模板ID必填')->keyText('name', '标题', '标题')->keyText('industry', '行业', '行业')
                    ->keyText('topcolor', '背景色', '背景色,默认值#FFFFFF,貌似此参数已无效')->keyTextArea('content', '详细内容', '保存下就为了看一看')
                    ->keyText('url', '详情URL', '详情URL')
                    ->data($tplmsg)
                    ->buttonSubmit(url('edit'))->buttonBack()
                    ->fetch();
        }
    }

    /**消息模板字段列表
     * @param $id
     * @author patrick<contact@uctoo.com>
     */
    public function field($id, $page = 1, $r = 20)
    {
        $tplmsg = db('Tplmsg')->where('id=' . $id)->find();
        $map['pid'] = $id;
        $field_list = db('Tplmsg_field')->where($map)->page($page, $r)->select();
        $totalCount = db('Tplmsg_field')->where($map)->count();
        $builder = new AdminListBuilder();
        $builder->title('【' . $tplmsg['name'] . '】 字段管理');
        $builder->meta_title = $tplmsg['name'] . '字段管理';
        $builder->buttonNew(url('Tplmsg/editField', array('pid' => $id)))->button('返回', array('href' => url('index')));
        $builder->keyId()->keyText('name', "字段名称")->keyText('value', '字段值')->keyDoAction('Tplmsg/editField?pid=' . $id . '&id=###', '编辑')->keyDoAction('delField?ids=###', '删除');
        $builder->data($field_list);
        $builder->pagination($totalCount, $r);
        $builder->display();
    }

    /**
     * 删除消息模板字段
     * @param null $id
     * @author patrick<contact@uctoo.com>
     */
    public function delField($ids = null){
        if (!$ids) {
            $this->error('请选择消息模板字段');
        }
        $model =  db('Tplmsg_field');
        $res = $model->delete($ids);
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    public function editField($id = 0, $pid = 0,$mp_id = 0,$template_id = '',$name = '',$value = '',$color = '#173177')
    {
        $model = db('Tplmsg_field');
        if (request()->isPost()) {   //提交表单
            $data['pid'] = $pid;
            if ($data['pid'] == '') {
                $this->error('模板ID不能为空！');
            }
            $data['mp_id'] = $mp_id;
            $data['template_id'] = $template_id;
            $data['name'] = $name;
            $data['value'] = $value;
            $data['color'] = $color;

            if ($id != 0) {
                $res = db('Tplmsg_field')->where('id=' . $id)->save($data);
            } else {
                $fmap['pid'] = $pid;
                $fmap['name'] = $name;
                if (db('Tplmsg_field')->where($fmap)->count() > 0) {
                    $this->error('该模板消息下已经有同名字段，请使用其他名称！');
                }
                $res = db('Tplmsg_field')->add($data);
            }
            if ($res) {
                $this->success($id == '' ? "添加模板消息字段成功" : "编辑模板消息字段成功", url('field', array('id' => $pid)));
            } else {
                $this->error($id == '' ? "添加模板消息字段失败" : "编辑模板消息字段失败");
            }

        }else{   //显示表单
            //读取模板消息字段信息
            if ($id != 0) {  //编辑
                $tplmsg_field = $model->where(array('id' => $id))->find();
            } else {  //新增
                $tplmsg = db('Tplmsg')->where('id=' . $pid)->find();
                $tplmsg_field = array('pid' => $pid,'mp_id' => get_mpid(),'template_id' => $tplmsg['template_id'],'color' => '#173177');
            }

            //显示页面
            $builder = new AdminConfigBuilder();
            $builder->title($id != 0 ? '编辑模板消息字段' : '添加模板消息字段')
                ->keyId()->keyHidden('pid','')->keyHidden('mp_id','')->keyHidden('template_id','')
                ->keyText('name', '模板消息字段名', '模板消息字段名')->keyTextArea('value', '模板消息字段值', '模板消息字段值,支持型如{$nickname}的变量,hook("TplMsg",$params)参数中的同名变量会自动替换')->keyText('color', '字体颜色', '字体颜色，默认值#173177')
                ->data($tplmsg_field)
                ->buttonSubmit(url('editField'))->buttonBack()
                ->display();
        }
    }
}
