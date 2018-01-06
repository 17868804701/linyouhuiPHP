<?php
// +----------------------------------------------------------------------
// | UCToo [ Universal Convergence Technology ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2017 http://uctoo.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Patrick <contact@uctoo.com>
// +----------------------------------------------------------------------


namespace app\ucuser\controller;

use think\Controller;
use app\common\model\Ucuser;
use com\ErrCode;
use com\TPWechat;
use com\Wxauth;
/**
 * 前台业务逻辑都放在此
 * @var
 */

class Index extends Controller
{
    public $ucuser;
    protected $weObj;          //自动注入的wechat SDK实例,用于管理公众号，自定义微信会员卡、优惠券、运营人员与微会员互动等场景

    //TP5 的架构方法绑定（属性注入）的对象
    public function __construct(TPWechat $weObj)
    {
        $this->weObj = $weObj;
        parent::__construct();
    }

    public function index(){
        $mp_id = $params['mp_id'] = $map['mp_id'] = get_mpid();
        $this->assign ( 'mp_id', $params['mp_id'] );
        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        $this->assign ( 'appinfo', $appinfo );
        $openid = get_openid();
        trace('get_index','info');
        trace($openid,'info');
        $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        if($mid === false){
            $this->error('只可在微信中访问');
        }
        trace('get_ucuser_mid111','info');
        trace($mid,'info');
        $user = get_mid_ucuser($mid);                    //获取本地存储公众号粉丝用户信息

        $url = request()->url(true);
        trace('ucuserindex','info');
        trace($url,'info');
        trace($user,'info');

        $surl = get_shareurl();
        if(!empty($surl)){
            $this->assign ( 'share_url', $surl );
        }

        $auth = $this->weObj->checkAuth();
        $js_ticket = $this->weObj->getJsTicket();
        if (!$js_ticket) {
            $this->error('获取js_ticket失败！错误码：'.$this->weObj->errCode.' 错误原因：'.ErrCode::getErrText($this->weObj->errCode));
        }
        $js_sign = $this->weObj->getJsSign($url);

        $this->assign ( 'js_sign', $js_sign );

        $fans = $this->weObj->getUserInfo($user['openid']);
        if($user['status'] != 2 && !empty($fans['openid'])){      //没有同步过用户资料，同步到本地数据
            $user = array_merge($user->toArray() ,$fans);
            $user['status'] = 2;
            $model = model('Ucuser');
            $model->save($user,['mid' => $user['mid']]);
        }
        if($user['login'] == 1){              //登录状态就显示微信的用户资料，未登录状态显示本地存储的用户资料
            if(!empty($fans['openid'])){
                $user = array_merge($user ,$fans);
            }
        }
        $this->assign ( 'user', $user );

        $member = get_member_by_openid($user["openid"]);          //获取会员信息
        //$score = model('Ucenter/Score')->getUserScore($member['id'],1);//查积分
        $this->assign ( 'member', $member );
        //$this->assign ( 'score', $score );
        //$templateFile = $this->model ['template_list'] ? $this->model ['template_list'] : '';
        return $this->fetch ( );
    }

    public function login(){
        $mp_id = $params['mp_id'] = $map['mp_id'] = get_mpid();
        $this->assign ( 'mp_id', $params['mp_id'] );
        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        $this->assign ( 'appinfo', $appinfo );
        $openid = get_openid();
        trace('get_index','info');
        trace($openid,'info');
        $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        if($mid === false){
            $this->error('只可在微信中访问');
        }
        trace('get_ucuser_mid111','info');
        trace($mid,'info');
        $user = get_mid_ucuser($mid);                    //获取本地存储公众号粉丝用户信息

        $url = request()->url(true);
        trace('ucuserindex','info');
        trace($url,'info');
        trace($user,'info');

        $surl = get_shareurl();
        if(!empty($surl)){
            $this->assign ( 'share_url', $surl );
        }

        $auth = $this->weObj->checkAuth();
        $js_ticket = $this->weObj->getJsTicket();
        if (!$js_ticket) {
            $this->error('获取js_ticket失败！错误码：'.$this->weObj->errCode.' 错误原因：'.ErrCode::getErrText($this->weObj->errCode));
        }
        $js_sign = $this->weObj->getJsSign($url);

        $this->assign ( 'js_sign', $js_sign );
        if (request()->isPost()) {
            $aMobile = input('mobile');
            $aPassword = input('password');
            $aRemember = input('remember');

            $umap['mobile'] = $aMobile;
            //$umap['password'] = think_ucenter_md5($aPassword, UC_AUTH_KEY);
            $member = UCenterMember()->where($umap)->find();

            if (empty ($member)) {                                 //在pc端没注册，登录时不自动注册pc端帐号

            } else {
                //已经通过网站注册过帐号,v1.0微信端登录时不自动同步会员表密码
                /*
                if($member['password'] != $user['password']){
                    $data['mid'] = $mid;
                    $data['uid'] = $member['id'];                            //将UCenterMember表的id写入ucuser表uid字段
                    $data['mobile'] = $aMobile;
                    $data['password'] = $member['password'];              //同步加密后的密码
                    $ucuser = model('Ucuser');
                    $ucuser->isUpdate(true)->save($data);
                }*/
            }

            $ucuser = model('Ucuser');
            $res = $ucuser->login($mid,$aMobile,$aPassword,$aRemember);
            echo '$res粉丝ID：'.$res;
            if($res > 0){
                $this->success ( '登录成功', url('Ucuser/Index/index'));
                return ['data'=>[],'status'=>true,'message'=>'login success','url'=>url('Ucuser/Index/index')];
            }else{
                $this->error ( '登录失败','','',3 );
                return ['data'=>[],'status'=>false,'message'=>'login success','url'=>url('Ucuser/Index/index')];
            }

        } else { //显示登录页面
            return $this->fetch();
        }
    }

    public function register(){
        $mp_id = $params['mp_id'] = $map['mp_id'] = get_mpid();
        $this->assign ( 'mp_id', $params['mp_id'] );
        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        $this->assign ( 'appinfo', $appinfo );
        $openid = get_openid();
        trace('get_index','info');
        trace($openid,'info');
        $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        if($mid === false){
            $this->error('只可在微信中访问');
        }
        trace('get_ucuser_mid111','info');
        trace($mid,'info');
        $user = get_mid_ucuser($mid);                    //获取本地存储公众号粉丝用户信息

        $url = request()->url(true);
        trace('ucuserindex','info');
        trace($url,'info');
        trace($user,'info');

        $surl = get_shareurl();
        if(!empty($surl)){
            $this->assign ( 'share_url', $surl );
        }

        $auth = $this->weObj->checkAuth();
        $js_ticket = $this->weObj->getJsTicket();
        if (!$js_ticket) {
            $this->error('获取js_ticket失败！错误码：'.$this->weObj->errCode.' 错误原因：'.ErrCode::getErrText($this->weObj->errCode));
        }
        $js_sign = $this->weObj->getJsSign($url);

        $this->assign ( 'js_sign', $js_sign );

        if (request()->isPost()) {

            $aMobile = input('mobile');
            $aPassword = input('password');
            $rePassword = input('repassword');
//            $verify = input('post.verify', '', 'op_t');
            $aMid = input('mid');
            //读取SESSION中的验证信息
//            $mobile = session('reset_password_mobile');
//            //提交修改密码和接收验证码的手机号码不一致
//            if ($aMobile != $mobile) {
//                echo '提交注册的手机号码和接收验证码的手机号码不一致';
//                return false;
//            }
//            $res = db('Verify')->checkVerify($aMobile, "mobile", $verify, 0);
//            //确认验证信息正确
//            if(!$res){
//                echo  '验证码错误';
//                return false;
//            }else{
//
//            }

            //判断是否在pc端已注册
            $umap['mobile'] = $aMobile;
            $ucmember = UCenterMember()->where($umap)->find();
            if (empty ($ucmember)) {                                 //在pc端没注册，注册一个pc端帐号
                //先在Member表注册会员，公众号粉丝在绑定手机后可登录网站
                $aUsername = $aMobile;                  //以手机号作为默认UcenterMember用户名和Member昵称
                $aNickname = $aMobile;          //以手机号作为默认UcenterMember用户名和Member昵称
                $email = $user['openid'].$user['mp_id'].'.com';   //以openid@mp_id.com作为默认邮箱
                $aUnType = 5;                                           //微信公众号粉丝注册
                $aRole = 3;                                             //默认公众号粉丝用户角色
                /* 注册用户 */
                $uid = UCenterMember('logic')->register($aUsername, $aNickname, $aPassword, $email, $aMobile, $aUnType);
                if (0 < $uid) { //注册成功
                    UCenterMember('logic')->initRoleUser($aRole,$uid); //初始化角色用户
                    set_user_status($uid, 1);                           //微信注册的用户状态直接设置为1
                    $user['uid'] = $uid;                               //将member表的uid写入ucuser表uid字段
                    //model('Ucuser')->registerUser($this->ucuser->mid,$this->ucuser->openid);                    //同步微信资料
                    $res = model('Ucuser')->where(array('mid'=>$mid))->setField('uid',$uid);
                    if($res > 0){
                        $this->success ( '注册成功', url ( 'Ucuser/Index/login' ) );
                        return ['data'=>[],'status'=>true,'message'=>'register success','url'=>url('Ucuser/Index/login')];
                    }
                } else { //注册失败，返回错误信息

                    return ['data'=>[],'status'=>false,'message'=>'register error','url'=>url('Ucuser/Index/register')];
                }
            } else {                                                     //已经通过网站注册过帐号
                if(empty($user['uid'])){                                //会员帐号未与微会员关联
                    $data['mid'] = $mid;
                    $data['uid'] = $ucmember['id'];                            //将UCenterMember表的id写入ucuser表uid字段
                    $data['mobile'] = $aMobile;
                    $data['password'] = think_ucenter_md5($aPassword, UC_AUTH_KEY);   //将用户微信端注册密码写入ucuser，即会员和微会员密码可能不同
                    $res = model('Ucuser')->isUpdate(true)->save($data,['mid' => $mid]);
                }elseif($user['mobile'] == $aMobile){
                    return ['data'=>[],'status'=>false,'message'=>'mobile already register','url'=>url('Ucuser/Index/register')];
                }else{                                        //微会员即关联了会员帐号，注册号码又和已有微会员保存号码不同，TODO：

                }
                return ['data'=>[],'status'=>true,'message'=>'already register','url'=>url('Ucuser/Index/register')];
            }
        } else { //显示注册页面
            return $this->fetch();
        }

    }

    public function profile(){
        $mp_id = $params['mp_id'] = $map['mp_id'] = get_mpid();
        $this->assign ( 'mp_id', $params['mp_id'] );
        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        $this->assign ( 'appinfo', $appinfo );
        $openid = get_openid();
        trace('get_index','info');
        trace($openid,'info');
        $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        if($mid === false){
            $this->error('只可在微信中访问');
        }
        trace('get_ucuser_mid111','info');
        trace($mid,'info');
        $user = get_mid_ucuser($mid);                    //获取本地存储公众号粉丝用户信息

        $url = request()->url(true);
        trace('ucuserindex','info');
        trace($url,'info');
        trace($user,'info');

        $surl = get_shareurl();
        if(!empty($surl)){
            $this->assign ( 'share_url', $surl );
        }

        $auth = $this->weObj->checkAuth();
        $js_ticket = $this->weObj->getJsTicket();
        if (!$js_ticket) {
            $this->error('获取js_ticket失败！错误码：'.$this->weObj->errCode.' 错误原因：'.ErrCode::getErrText($this->weObj->errCode));
        }
        $js_sign = $this->weObj->getJsSign($url);

        $this->assign ( 'js_sign', $js_sign );

        $fans = $this->weObj->getUserInfo($user['openid']);
        $this->assign ( 'user', $user );

        if (request()->isPost()) {
            $data['mid'] = $mid;
            $data['nickname'] = input('post.nickname');
            $data['mobile'] = input('post.mobile');
            $data['email'] = input('post.email');
            $data['sex'] = input('post.sex');
            $data['qq'] = input('post.qq');
            $data['weibo'] = input('post.weibo');
            $data['signature'] = input('post.signature');
            $verify = input('post.verify');

            //读取SESSION中的验证信息
            $mobile = session('reset_password_mobile');
            //提交修改密码和接收验证码的手机号码不一致
            if ($data['mobile'] != $mobile) {
                echo '提交修改密码和接收验证码的手机号码不一致';
                return false;
            }
            $res = model('Verify')->checkVerify($data['mobile'], "mobile", $verify, 0);
            //确认验证信息正确
            if(!$res){
                echo  '验证码错误';
                return false;
            }else{

            }

            $ucuser = model('Ucuser');
            $res = $ucuser->save($data,['mid' => $mid]);
            if($res > 0){
                $this->success ( '更新资料成功', url ( 'Ucuser/Index/profile' ) );
            }else{
                $this->error ( $ucuser->getError () );
            }

        } else { //显示资料页面
            if($user['openid'] != $fans['openid']){        //本地保存的openid和公众平台获取的不同，不允许用户自己以外的人访问
                $this->error ( '无权访问用户资料',url ( 'Ucuser/Index/login' ),5 );
            }
            return $this->fetch();
        }
    }

    public function logout($mid = 0){
        if($mid == 0){
            $mid = get_ucuser_mid();
        }
        $ucuser = model('Ucuser');
        $ucuser->logout($mid);
        $this->success ( '已退出登录',  ( 'Ucuser/Index/index' ) );
    }

    public function forget(){

        $params['mp_id'] = $map['mp_id'] = get_mpid();
        $this->assign ( 'mp_id', $params['mp_id'] );
        $map['id'] = input('id');
        $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        if($mid === false){
            $this->error('只可在微信中访问');
        }
        $ucuser = get_mid_ucuser($mid);

        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        $this->assign ( 'appinfo', $appinfo );

        if (request()->isPost()) {
            $aMobile = input('post.mobile');
            $verify = input('post.verify');
            $password = input('post.password');
            $repassword = input('post.repassword');

            //确认两次输入的密码正确
            if ($password != $repassword) {
                $this->error('两次输入的密码不一致');
            }
            //读取SESSION中的验证信息
            $mobile = session('reset_password_mobile');
            //提交修改密码和接收验证码的手机号码不一致
            if ($aMobile != $mobile) {
                $this->error('提交修改密码和接收验证码的手机号码不一致');
            }

            $res = model('Verify')->checkVerify($aMobile, "mobile", $verify, 0);
            //确认验证信息正确
            if(!$res){
                echo '验证码错误';
                return false;
            }else{
                echo true;
            }

            //将新的密码写入数据库
            $data1 = array('mid' => $mid, 'mobile' => $aMobile, 'password' => $password);
            $model = model('Ucuser');
            $data1 = $model->save($data1,['mid'=>$mid]);
            if (!$data1) {
                $this->error('密码格式不正确');
            }
            $result = $model->where(array('mid' => $mid))->save($data1);
            if ($result === false) {
                $this->error('数据库写入错误');
            }

            //将新的密码写入数据库

            $data = array('id' => $ucuser['uid'], 'mobile' => $aMobile, 'password' => $password);
            $model = UCenterMember();
            $data = $model->save($data,['id'=>$ucuser['uid']]);
            if (!$data) {
                $this->error('密码格式不正确');
            }
            if ($result === false) {
                $this->error('数据库写入错误');
            }

            //显示成功消息
            $this->success('密码重置成功',  url ( 'Ucuser/Index/login' ) );

        }

        return $this->fetch();
    }

    /**
     * sendVerify 发送短信验证码
     * @author:patrick contact@uctoo.com
     *
     */
    public function sendVerify()
    {
        $mobile = input('post.mobile');

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
        $aMobile = input('post.mobile');
        $verify = input('post.verify');
        $aUid = input('mid');

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