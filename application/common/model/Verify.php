<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-26
 * Time: 下午4:29
 * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
 */

namespace app\common\model;

use think\Model;

define('NOW_TIME',time());
class Verify extends Model
{
    protected $tableName = 'verify';
    protected $_auto = array(array('create_time', NOW_TIME, self::MODEL_INSERT));



    public function addVerify($account, $type, $uid = 0)
    {

        $aVerify = input('post.verify', '', 'text');
        if (empty($aVerify)) {
            $this->error = '验证码不能为空';
            return false;
        }

        $varify_id = $type=='email'? 3 : 2;
        if (!check_verify($aVerify,$varify_id)) {
            $this->error =  lang('_ERROR_VERIFY_CODE_').lang('_PERIOD_');
            return false;
        }



        $uid = $uid ? $uid : is_login();
        if ($type == 'mobile' || (modC('EMAIL_VERIFY_TYPE', 0, 'USERCONFIG') == 2 && $type == 'email')) {
            $verify = create_rand(6, 'num');
        } else {
            $verify = create_rand(32);
        }
        $this->where(array('account' => $account, 'type' => $type))->delete();
        $data['verify'] = $verify;
        $data['account'] = $account;
        $data['type'] = $type;
        $data['uid'] = $uid;
        $data = $this->create($data);
        $res = $this->save($data);
        if (!$res) {
            $this->error = '';
            return false;
        }
        return $verify;
    }


    public function addSMSVerify($account,$verify,$type="mobile",$uid=0)
    {
        $uid = $uid?$uid:is_login();

        $this->where(array('account'=>$account,'type'=>$type))->delete();
        $data['verify'] = $verify;
        $data['account'] = $account;
        $data['type'] = $type;
        $data['uid'] = $uid;
        //$data = $this->create($data);
        $res = $this->save($data);
        if(!$res){
            return false;
        }
        return $verify;
    }


    public function getVerify($id)
    {
        $verify = $this->where(array('id' => $id))->getField('verify');
        return $verify;
    }

    public function checkVerify($account, $type, $verify, $uid)
    {
        $verify1 = $this->where(array('account' => $account, 'type' => $type, 'verify' => $verify, 'uid' => $uid))->select();
        if (!$verify1) {
            return false;
        }
        $this->where(array('account' => $account, 'type' => $type))->delete();
        //$this->where('create_time <= '.get_some_day(1))->delete();

        return true;
    }

}















