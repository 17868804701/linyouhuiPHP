<?php
/**
 * Created by PhpStorm.
 * User: UCT
 * Date: 2017/2/20
 * Time: 10:19
 */
namespace app\common\model;
use think\Model;
use think\Request;


class UcenterMember extends Model{

    // 定义时间戳字段名
    protected $createTime = 'reg_time';
    protected $updateTime = 'update_time';
    protected $insert = [
        'type'=>1,
    ];


    /**
     * 注册方法
     * @param $data
     * @return false|int
     */
    function register($data){
        $ret = $this->where('mobile',$data['mobile'])->find();
        if ($ret){
            return 2;
        }

        if ($data['password'] != $data['password1']){
            return 3;
        }else{
            unset($data['password1']);
            $data['password'] = think_ucenter_md5($data['password'], UC_AUTH_KEY);
            return $this->save($data);
        }


    }

    /**
     * login  用户登录方法
     * @param $username
     * @param $password
     * @return mixed
     */
    function login($mobile,$password){
        $where = array(
            'mobile'=>$mobile,
            'password' => think_ucenter_md5($password, UC_AUTH_KEY),
        );

        $ret = $this->where($where)->find();
        if ($ret){
            session('user',$ret);
            //更新登录信息
            $data['last_login_time']  = $_SERVER['REQUEST_TIME'];
            $data['last_login_ip']  = Request::instance()->ip();

            $this->isUpdate(true)->save($data,['username'=>$mobile]);  // 显式指定更新数据操作
            return $ret['id']; //登录成功，返回用户ID
        }else{
            redirect("{:url('register')}");
        }
    }

    /**
     * edit编辑个人资料
     * @param $uid
     */
    function edit($uid){

    }
}
