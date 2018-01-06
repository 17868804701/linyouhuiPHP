<?php
/**
 * Created by PhpStorm.
 * User: uctoo
 * Date: 2017/2/13
 * Time: 16:30
 */
namespace app\api\Controller;
use think\Controller;
use think\Request;

class Verify extends Controller{

    function index(){

    }
    /**
     * sendVerify 发送短信验证码
     * @author:patrick contact@uctoo.com
     *
     */
    public function sendVerify()
    {
        $mobile = input('get.mobile', '', 'op_t');

        if (empty($mobile)) {
            $this->error('手机号不能为空');
        }

        //保存SESSION中的验证手机号码
        session('reset_password_mobile',$mobile);

        $res = sendSMS($mobile,"");
        echo $res;             //ajax 返回提示
    }

    /**
     * checkVerify 检测验证码
     * @author:patrick contact@uctoo.com
     *
     */
    public function checkVerify()
    {
        $aMobile = input('post.mobile', '', 'op_t');
        $verify = input('post.verify', '', 'op_t');
        $aUid = input('mid', 0, 'intval');

        //读取SESSION中的验证信息
        $mobile = session('reset_password_mobile');
        //提交修改密码和接收验证码的手机号码不一致
        if ($aMobile != $mobile) {
            echo '提交注册的手机号码和接收验证码的手机号码不一致';
            return false;
        }

        $res = model('Verify')->checkVerify($aMobile, "mobile", $verify, 0);

        if (!$res) {
            echo '验证码错误';
            return false;
        }else{
            echo true;
        }
    }
}