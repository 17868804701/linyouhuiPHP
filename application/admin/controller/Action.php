<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace app\admin\Controller;
use app\admin\builder\AdminListBuilder;
use app\admin\controller\Admin;

/**
 * 行为控制器
 * @author huajie <banhuajie@163.com>
 */
class Action extends Admin {

    /**
     * 行为日志列表
     * @author huajie <banhuajie@163.com>
     */
    public function actionLog(){
        //获取列表数据
        $aUid=input('get.uid',0,'intval');
        if($aUid) $map['user_id']=$aUid;
        $map['status']    =   array('gt', -1);
        $list   =   db('ActionLog')->where($map)->select();//paginate(10);
        int_to_string($list);
        foreach ($list as $key=>$value){
            $model_id                  =   get_document_field($value['model'],"name","id");
            $list[$key]['model_id']    =   $model_id ? $model_id : 0;
        }

        $this->assign('_list', $list);
        $this->meta_title = lang('_BEHAVIOR_LOG_');
        return $this->fetch();
    }
    public function scoreLog($r=20,$p=1){

        if(input('type')=='clear'){
            model('ScoreLog')->where(array('id>0'))->delete();
            $this->success('清空成功。',url('scoreLog'));
            exit;
        }else{
            $aUid=input('uid',0,'');
            if($aUid){
                $map['uid']=$aUid;
            }
            $listBuilder=new AdminListBuilder();
            $listBuilder->title('积分日志');
            $map['status']    =   array('gt', -1);
            $scoreLog=model('ScoreLog')->where($map)->order('create_time desc')->findPage($r);

            $scoreTypes=model('Ucenter/Score')->getTypeListByIndex();
            foreach ($scoreLog['data'] as &$v) {
                $v['adjustType']=$v['action']=='inc'?'增加':'减少';
                $v['scoreType']=$scoreTypes[$v['type']]['title'];
                $class=$v['action']=='inc'?'text-success':'text-danger';
                $v['value']='<span class="'.$class.'">' .  ($v['action']=='inc'?'+':'-'). $v['value']. $scoreTypes[$v['type']]['unit'].'</span>';
                $v['finally_value']= $v['finally_value']. $scoreTypes[$v['type']]['unit'];
            }


            $listBuilder->data($scoreLog['data']);

            $listBuilder->keyId()->keyUid('uid','用户')->keyText('scoreType','积分类型')->keyText('adjustType','调整类型')->keyHtml('value','积分变动')->keyText('finally_value','积分最终值')->keyText('remark','变动描述')->keyCreateTime();
            $listBuilder->pagination($scoreLog['count'],$r);
            $listBuilder->search(lang('_SEARCH_'),'uid','text','输入UID');

            $listBuilder->button('清空日志',array('url'=>url('scoreLog',array('type'=>'clear')),'class'=>'btn ajax-get confirm'));
            $listBuilder->display();
        }



    }

    /**
     * 查看行为日志
     * @author huajie <banhuajie@163.com>
     */
    public function edit($id = 0){
        empty($id) && $this->error(lang('_PARAMETER_ERROR_'));

        $info = db('ActionLog')->field(true)->find($id);

        $this->assign('info', $info);
        $this->meta_title = lang('_CHECK_THE_BEHAVIOR_LOG_');
        $this->display();
    }

    /**
     * 删除日志
     * @param mixed $ids
     * @author huajie <banhuajie@163.com>
     */
    public function remove($ids = 0){
        empty($ids) && $this->error(lang('_PARAMETER_ERROR_'));
        if(is_array($ids)){
            $map['id'] = array('in', $ids);
        }elseif (is_numeric($ids)){
            $map['id'] = $ids;
        }
        $res = db('ActionLog')->where($map)->delete();
        if($res !== false){
            $this->success(lang('_DELETE_SUCCESS_'));
        }else {
            $this->error(lang('_DELETE_FAILED_'));
        }
    }

    /**
     * 清空日志
     */
    public function clear(){
        $res = db('ActionLog')->where('1=1')->delete();
        if($res !== false){
            $this->success(lang('_LOG_EMPTY_SUCCESSFULLY_'));
        }else {
            $this->error(lang('_LOG_EMPTY_'));
        }
    }

    //限制动作
    public function actionlimit()
    {
        $action_name = input('get.action','','op_t') ;
        !empty($action_name) && $map['action_list'] = array(array('like', '%[' . $action_name . ']%'),'','or');
        //读取规则列表
        $map['status'] = array('EGT', 0);
        $model = db('action_limit');
        $List = $model->where($map)->order('id asc')->select();
//        $timeUnit = $this->getTimeUnit();
//        foreach($List as &$val){
//            $val['time'] =$val['time_number']. $timeUnit[$val['time_unit']];
//            $val['action_list'] = get_action_name($val['action_list']);
//            empty( $val['action_list']) &&  $val['action_list'] = lang('_ALL_ACTS_');
//
//            $val['punish'] = get_punish_name($val['punish']);
//
//
//        }
        unset($val);
        //显示页面
        $builder = new AdminListBuilder();
        return  $builder->title(lang('_ACTION_LIST_'))
            ->buttonNew(url('editLimit'))
            ->setStatusUrl(url('setLimitStatus'))->buttonEnable()->buttonDisable()->buttonDelete()
            ->keyId()
            ->keyTitle()
            ->keyText('name', lang('_NAME_'))
            ->keyText('frequency', lang('_FREQUENCY_'))
            ->keyText('time', lang('_TIME_UNIT_'))
            ->keyText('punish', lang('_PUNISHMENT_'))
            ->keyBool('if_message', lang('_SEND_REMINDER_'))
            ->keyText('message_content', lang('_MESSAGE_PROMPT_CONTENT_'))
            ->keyText('action_list', lang('_ACT_'))
            ->keyStatus()
            ->keyDoActionEdit('editLimit?id=###')
            ->data($List)
            ->pagination(10,10)
            ->fetch();
    }

    public function editLimit()
    {
        $aId = input('id', 0, 'intval');
        $model = model('ActionLimit');
        if (request()->IsPost()) {

            $data['title'] = input('post.title', '', 'op_t');
            $data['name'] = input('post.name', '', 'op_t');
            $data['frequency'] = input('post.frequency', 1, 'intval');
            $data['time_number'] = input('post.time_number', 1, 'intval');
            $data['time_unit'] = input('post.time_unit', '', 'op_t');
            $data['punish'] = input('post.punish', '', 'op_t');
            $data['if_message'] = input('post.if_message', '', 'op_t');
            $data['message_content'] = input('post.message_content', '', 'op_t');
            $data['action_list'] = input('post.action_list', '', 'op_t');
            $data['status'] = input('post.status', 1, 'intval');
            $data['module'] = input('post.module', '', 'op_t');

            $data['punish'] = implode(',', $data['punish']);

            foreach($data['action_list'] as &$v){
                $v = '['.$v.']';
            }
            unset($v);
            $data['action_list'] = implode(',', $data['action_list']);
            if ($aId != 0) {
                $data['id'] = $aId;
                $res = $model->editActionLimit($data);
            } else {
                $res = $model->addActionLimit($data);
            }
            if($res){
                $this->success(($aId == 0 ? lang('_ADD_') : lang('_EDIT_')) . lang('_SUCCESS_'), $aId == 0 ? url('', array('id' => $res)) : '');
            }else{
                $this->error($aId == 0 ? lang('_THE_OPERATION_FAILED_') : lang('_THE_OPERATION_FAILED_VICE_'));
            }
        } else {
            $builder = new AdminConfigBuilder();

            $modules = model('Module')->getAll();
            $module['all'] = lang('_TOTAL_STATION_');
            foreach($modules as $k=>$v){
                $module[$v['name']] = $v['alias'];
            }

            if ($aId != 0) {
                $limit = $model->getActionLimit(array('id' => $aId));
                $limit['punish'] = explode(',', $limit['punish']);
                $limit['action_list'] = str_replace('[','',$limit['action_list']);
                $limit['action_list'] = str_replace(']','',$limit['action_list']);
                $limit['action_list'] = explode(',', $limit['action_list']);

            } else {
                $limit = array('status' => 1,'time_number'=>1);
            }
            $opt_punish = $this->getPunish();
            $opt = model('Action')->getActionOpt();
            $builder->title(($aId == 0 ? lang('_NEW_') : lang('_EDIT_')) . lang('_ACT_RESTRICTION_'))->keyId()
                ->keyTitle()
                ->keyText('name', lang('_NAME_'))
                ->keySelect('module', lang('_MODULE_'),'',$module)
                ->keyText('frequency', lang('_FREQUENCY_'))
                // ->keySelect('time_unit', lang('_TIME_UNIT_'), '', $this->getTimeUnit())
                ->keyMultiInput('time_number|time_unit',lang('_TIME_UNIT_'),lang('_TIME_UNIT_'),array(array('type'=>'text','style'=>'width:295px;margin-right:5px'),array('type'=>'select','opt'=>$this->getTimeUnit(),'style'=>'width:100px')))

                ->keyChosen('punish', lang('_PUNISHMENT_'), lang('_MULTI_SELECT_'), $opt_punish)
                ->keyBool('if_message', lang('_SEND_REMINDER_'))
                ->keyTextArea('message_content', lang('_MESSAGE_PROMPT_CONTENT_'))
                ->keyChosen('action_list', lang('_ACT_'), lang('_MULTI_SELECT_DEFAULT_'), $opt)
                ->keyStatus()
                ->data($limit)
                ->buttonSubmit(url('editLimit'))->buttonBack()->display();
        }
    }






}
