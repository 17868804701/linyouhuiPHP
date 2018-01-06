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

namespace app\mpbase\controller;

//use app\api\controller\OrderGoods;
use com\wxpay\database\WxPayRefund;
use think\Controller;
use think\Request;
use com\TPWechat;
use com\wxpay\database\WxPayUnifiedOrder;
use com\wxpay\JsApiPay;
use com\wxpay\NativePay;
use com\wxpay\WxPayApi;
use com\wxpay\WxPayConfig;
use app\mpbase\controller\PayNotifyCallBack;
use think\Log;
use com\wxapp\wxBizDataCrypt;
use app\common\model\Session;
use app\common\model\Ucuser;
use app\common\model\ShopOrder as Order;
use app\common\model\OrderGoods;

/**
 * 微信小程序消息推送控制器，启用并设置消息推送配置后，用户发给小程序的消息以及开发者需要的事件推送，都将被微信转发至该服务器地址中
 * 主要获取和反馈微信小程序平台的数据，分析用户交互和系统消息分发。
 */
class Wxapp extends Controller {

    protected $weObj;          //自动注入的wechat SDK实例

    //TP5 的架构方法绑定（属性注入）的对象
    public function __construct(TPWechat $weObj)
    {
        $this->weObj = $weObj;
        trace('__construct in Mpbase','info');
        trace($weObj,'info');
        parent::__construct();
    }

    /**
     * 微信小程序消息推送配置
     * 所有发送到微信小程序第三方服务器的消息都会推送到该操作
     * 所以，微信公众平台小程序后台填写的api地址则为该操作的访问地址
     * 在mp.weixin.qq.com 开发者中心配置的 URL(服务器地址)  http://域名/index.php/mpbase/wxapp/index/mp_id/member_public表的mp_id.html
     */
    public function index($mp_id = '') {
        trace($mp_id,'wxapp_debugmp_id');
        //设置当前上下文的公众号mp_id
        $mp_id = get_mpid($mp_id);
        $this->weObj->valid();
        $this->weObj->getRev();
        $data = $this->weObj->getRevData();
        $type = $this->weObj->getRevType();
        $ToUserName = $this->weObj->getRevTo();
        $FromUserName = $this->weObj->getRevFrom();
        $params['weObj'] = $this->weObj;
        $params['mp_id'] = $mp_id;

        if (config('app_debug')) { // 是否开发者模式
            $mid = 0;
            addWeixinLog ( $data, Request::instance()->request(false),$mp_id, $mid ,$ToUserName,$FromUserName,$type);
        }
        //如果被动响应可获得用户信息就记录下
        if (! empty ( $ToUserName )) {
            get_token ( $ToUserName );
        }
        if (! empty ( $FromUserName )) {
            get_openid($FromUserName);
        }
        $html = 'here is html';
        // 调用temphook钩子, 实现钩子业务
        hook('temphook', ['data'=>$html]);
        hook('initUcuser',$params);   //执行addons/ucuser/Ucuser/initUcuser的方法,初始化公众号粉丝信息
        trace($params,'after_initUcuser');
        $map['openid'] = get_openid();
        $map['mp_id'] = $params['mp_id'];
        $ucuser = model('Ucuser');
        $user = $ucuser->where($map)->find();       //查询出公众号的粉丝
        $fsub = $user["subscribe"];               //记录首次关注状态
        $mid = $user["mid"];
        //与微信交互的中控服务器逻辑可以自己定义，这里实现一个通用的
        switch ($type) {
            //事件
            case TPWechat::MSGTYPE_EVENT:         //先处理事件型消息
                $event = $this->weObj->getRevEvent();

                switch ($event['event']) {
                    //关注
                    case TPWechat::EVENT_SUBSCRIBE:

                        //二维码关注
                        if(isset($event['eventkey']) && isset($event['ticket'])){

                            //普通关注
                        }else{

                        }

//                        $weObj->reply();
                        //获取回复数据
                        $where['mp_id']=get_mpid();
                        $where['mtype']= 1;
                        $where['statu']= 1;
                        $model_re = model('Mpbase/ReplayMessages');
                        $data_re=$model_re->where($where)->find();
                        $params['type']=$data_re['type'];
                        trace('inininEVENT_SUBSCRIBE','info');
                        trace($data_re,'info');
                        trace(!$data_re,'info');
                        if(!$data_re){
                            $params['replay_msg']=model('Mpbase/Autoreply')->get_type_data($data_re);
                            $model_re->wxmsg($params);
                        }

//                        hook('wxmsg',$params);

                        $this->weObj->reply();

                        if(!$user["subscribe"]){   //未关注，并设置关注状态为已关注
                            $user["subscribe"] = 1;
                            $ucuser->where($map)->update($user);
                        }

                        exit;
                        break;
                    //扫描二维码
                    case TPWechat::EVENT_SCAN:

                        break;
                    //地理位置
                    case TPWechat::EVENT_LOCATION:

                        break;
                    //自定义菜单 - 点击菜单拉取消息时的事件推送
                    case TPWechat::EVENT_MENU_CLICK:

//                        hook('keyword',$params);   //把消息分发到实现了keyword方法的addons中,参数中包含本次用户交互的微信类实例和公众号在系统中id
//                        $weObj->reply();           //在addons中处理完业务逻辑，回复消息给用户
                        $where['keywork']=array('like', '%' . $data['Content'] . '%');
                        $where['mtype']= 3;
                        $where['statu']= 1;
                        $where['mp_id']=get_mpid();
                        $model_re = model('Mpbase/ReplayMessages');
                        $data_re=$model_re->where($where)->find();
                        $params['type']=$data_re['type'];
                        trace('inininEVENT_MENU_CLICK','info');
                        trace($data_re,'info');
                        trace(!$data_re,'info');
                        if($data_re) {
                            $params['replay_msg'] = model('Autoreply')->get_type_data($data_re);
                            $model_re->wxmsg($params);
                        }
//                        hook('wxmsg',$params);

                        $this->weObj->reply();  //在addons中处理完业务逻辑，回复消息给用户
                        break;

                    //自定义菜单 - 点击菜单跳转链接时的事件推送
                    case TPWechat::EVENT_MENU_VIEW:

                        break;
                    //自定义菜单 - 扫码推事件的事件推送
                    case TPWechat::EVENT_MENU_SCAN_PUSH:

                        break;
                    //自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
                    case TPWechat::EVENT_MENU_SCAN_WAITMSG:

                        break;
                    //自定义菜单 - 弹出系统拍照发图的事件推送
                    case TPWechat::EVENT_MENU_PIC_SYS:

                        break;
                    //自定义菜单 - 弹出拍照或者相册发图的事件推送
                    case TPWechat::EVENT_MENU_PIC_PHOTO:

                        break;
                    //自定义菜单 - 弹出微信相册发图器的事件推送
                    case TPWechat::EVENT_MENU_PIC_WEIXIN:

                        break;
                    //自定义菜单 - 弹出地理位置选择器的事件推送
                    case TPWechat::EVENT_MENU_LOCATION:

                        break;
                    //取消关注
                    case TPWechat::EVENT_UNSUBSCRIBE:
                        if($user["subscribe"]){
                            $user["subscribe"] = 0;     //取消关注设置关注状态为取消
                            $ucuser->where($map)->update($user);
                        }

                        break;
                    //群发接口完成后推送的结果
                    case TPWechat::EVENT_SEND_MASS:

                        break;
                    //模板消息完成后推送的结果
                    case TPWechat::EVENT_SEND_TEMPLATE:

                        break;
                    default:

                        break;
                }
                break;
            //文本
            case TPWechat::MSGTYPE_TEXT :

                $where['keywork']=array('like', '%' . $data['Content'] . '%');
                $where['mtype']= 3;
                $where['statu']= 1;
                $where['mp_id']=get_mpid();
                $model_re = model('Mpbase/ReplayMessages');
                $data_re=$model_re->order('time desc')->where($where)->find();
//              关键字匹配失败进入自动回复
                if($data_re){
                    unset($where);
                    $where['mtype']= 2;
                    $where['statu']= 1;
                    $where['mp_id']=get_mpid();
                    $data_re=$model_re->order('time desc')->where($where)->find();
                }

                $params['type']=$data_re['type'];
                trace('inininMSGTYPE_TEXT','info');
                trace($data_re,'info');
                trace(!$data_re,'info');
                if($data_re) {
                    $params['replay_msg'] = model('Autoreply')->get_type_data($data_re);
                    $model_re->wxmsg($params);
                }
//                hook('wxmsg',$params);

                $this->weObj->reply();  //在addons中处理完业务逻辑，回复消息给用户
                break;
            //图像
            case TPWechat::MSGTYPE_IMAGE :

                break;
            //语音
            case TPWechat::MSGTYPE_VOICE :

                break;
            //视频
            case TPWechat::MSGTYPE_VIDEO :

                break;
            //位置
            case TPWechat::MSGTYPE_LOCATION :

                break;
            //链接
            case TPWechat::MSGTYPE_LINK :

                break;
            default:

                break;
        }

        // 记录日志

        if (config('app_debug')) { // 是否开发者模式
            addWeixinLog ( $data, Request::instance()->request(false),$mp_id, $mid ,$ToUserName,$FromUserName,$type);
        }
    }

     /**
     * 微信消息接口入口
     * 所有发送到微信的消息都会推送到该操作
     * 所以，微信公众平台后台填写的api地址则为该操作的访问地址
     * 在mp.weixin.qq.com 开发者中心配置的 URL(服务器地址)  http://域名/index.php/mpbase/weixin/index/mp_id/member_public表的mp_id.html
     */
	public function onLogin($mp_id) {
        trace($mp_id,'输出MPID');
        //设置当前上下文的公众号mp_id
        $code = input('code');
        //用户信息
        $rawData = input("rawData");
        $rawData1 = json_decode($rawData,true);
        $signature = input("signature", '', 'htmlspecialchars_decode');
        $encryptedData = input("encryptedData", '', 'htmlspecialchars_decode');
        $iv = input("iv", '', 'htmlspecialchars_decode');

        trace($rawData,'用户信息');
        trace($code,'onLogin_code');
        $appinfo = get_mpid_appinfo($mp_id);
        $json = $this->weObj->getWxappSession($code);
        trace($json,'onLogin_json');
        if (!$json) {
            return false;
        }

        //返回用户session_key   openid
        $openid = $json['openid'];
        $session_key = $json['session_key'];

        //验证数据可信度
        $signature2 = sha1( $rawData .$session_key );
        if ($signature !== $signature2) {                            //数据错误
            return false;
        }
        //解密传输过来的用户信息数据
        $pc = new wxBizDataCrypt($appinfo->appid, $session_key);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );
        $userInfo = json_decode($data);
        trace($userInfo,'解密');
        if ($errCode == 0) {  //将用户信息保存到ucuser表
            $ucuser = Ucuser::get(['openid'=>$userInfo->openId]);
            if ($ucuser){
                //更新数据
                $ucuser->nickname = $userInfo->nickName;
                $ucuser->sex = $userInfo->gender;
                $ucuser->language = $userInfo->language;
                $ucuser->city = $userInfo->city;
                $ucuser->province = $userInfo->province;
                $ucuser->country = $userInfo->country;
                $ucuser->headimgurl = $userInfo->avatarUrl;
                //$ucuser->unionid = $userInfo->unionId;
                $ucuser->save();
            }else{      //插入新数据
                $ucuser = new Ucuser();
                $ucuser->nickname = $userInfo->nickName;
                $ucuser->sex = $userInfo->gender;
                $ucuser->language = $userInfo->language;
                $ucuser->city = $userInfo->city;
                $ucuser->province = $userInfo->province;
                $ucuser->country = $userInfo->country;
                $ucuser->headimgurl = $userInfo->avatarUrl;
                $ucuser->openid = $userInfo->openId;
                $ucuser->save();
            }
            $user_mid = $ucuser->mid;//获取当前用户的MID

            //记录行为日志
            $action = array();
            $action['action_id'] = $user_mid;
            $action['user_id'] = $user_mid;
            $action['action_ip'] = get_client_ip(1);
            $action['model'] = $userInfo->nickName;
            $action['remark'] = $userInfo->nickName.'在'.date('Y-m-d H:i:s',time()).'登陆了账号';
            $action['create_time'] = time();
//            db('ActionLog')->insert($action);
        } else {
            return $errCode;
        }
        //生成3rd_session真随机数
        $userInfo->session_3rd = $session_3rd = bin2hex(random_bytes(16));
        $userInfo->mid = $user_mid;//返回用户标识,后续使用
        trace($session_3rd,'after_initUcuser');
        //以3rd_session为key,session_key+openid为value，写入session存储
        $session = new Session();
        $session->session_id = $session_3rd;
        $session->session_data = json_encode(['session_key'=>$session_key,'openid'=>$openid]);
        //$session->openid = $openid;
        $session->save();
        //将3rd_session返回给小程序端。小程序端应将3rd_session保存至storage，后续用户进入小程序，先从storage读取3rd_session
        //根据3rd_session在session存储中查找合法的session_key和openid
       // $ret = ['session_3rd'=>$session_3rd];
       // trace(json($ret),'onLogin_end');
        return json($userInfo);
	}

    //与小程序端wx.checkSession方法对应，用于小程序端调用的接口
    public function checkSession(){
        $session_3rd = input('session_3rd');
        trace($session_3rd , 'checkSessionsession_3rd');
        if(empty($session_3rd)){
            return false;
        }
        $se3rd = Session::get($session_3rd);
        if (!$se3rd){
            return false;
        }
        trace($se3rd , 'checkse3rd');
        $session_key = json_decode($se3rd->session_data);
        trace($session_key , 'checkSessionsession_key');
        if (empty ( $session_key )) {
            return false;
        }
        return true;
    }

    //与小程序端wx.getUserInfo方法对应使用，同步小程序用户信息到ucuser表
    public function setUserInfo(){
        $mp_id = input('mp_id');
        $session_3rd = input('session_3rd');
        $rawData = input('rawData');
        $signature = input('signature');
        $encryptedData = input('encryptedData');
        $iv = input('iv');

        $appinfo = get_mpid_appinfo($mp_id);
        //检测$session_3rd参数是否有效，如无有效$session_3rd参数，则小程序端需要再次调用onLogin接口获取$session_3rd
        $session_key = checkSession();
        if(!$session_key){
            return false;
        }
        //校验数据完整性
        $signature2 = sha1( $rawData .$session_key );
        if ($signature == $signature2) {      //数据正确

        } else {                              //数据错误
            return false;
        }
        //解密传输过来的用户信息数据
        $pc = new wxBizDataCrypt($appinfo->appid, $session_key);
        $errCode = $pc->decryptData($encryptedData, $iv, $rawData );
        $userInfo = json_decode($rawData);
        if ($errCode == 0) {  //将用户信息保存到ucuser表
          $ucuser = Ucuser::get(['openid'=>$userInfo->openId]);
          $ucuser->nickname = $userInfo->nickName;
          $ucuser->sex = $userInfo->gender;
          $ucuser->language = $userInfo->language;
          $ucuser->city = $userInfo->city;
          $ucuser->province = $userInfo->province;
          $ucuser->country = $userInfo->country;
          $ucuser->headimgurl = $userInfo->avatarUrl;
          $ucuser->unionid = $userInfo->unionId;
          $ucuser->save();
        } else {
            return $errCode;
        }
    }

    /**
     * 微信小程序支付统一下单获取支付参数方法
     * 在微信小程序前端需要进行微信支付的地方通过request接口调用https://www.huaict.com/mpbase/wxapp/wxpay/mp_id/公众号表mp_id字段/id/
     * id参数带上order表的order_id参数请求此地址即可。
     * @return json
     */
    public function wxpay($id)
    {
        $orderData = Order::get(['order_sn'=>$id]);
        trace($orderData,'支付返回');
        //检查规格数量


        trace($orderData,'wxpayorder');
        $mp_id = $params['mp_id'] = $map['mp_id'] = get_mpid();
        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        trace($mp_id,'info');
        //        $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        $cfg = array(
            'APPID'     => $appinfo['appid'],
            'MCHID'     => $appinfo['mchid'],
            'KEY'       => $appinfo['mchkey'],
            'APPSECRET' => $appinfo['secret'],
            'NOTIFY_URL' => $appinfo['notify_url'],
        );
        WxPayConfig::setConfig($cfg);

        if (isset($id) && $id != 0) {
            //商户订单
            $outid = $orderData->id.date("YmdHis",time()).rand(0,9);
            $orderData->order_sn = $outid;
            $orderData->save();
            //获取用户openid
            $tools = new JsApiPay();
            trace($tools,'wxpayJsApiPay');
            //统一下单
            $input = new WxPayUnifiedOrder();
            $input->setBody($orderData->order_sn);
            $input->setAttach($orderData->user_id);
            $input->setOutTradeNo($outid);  //建议默认的预支付交易单商户订单号由date("YmdHis").'_'.order_id组成
            $input->setTotalFee(intval($orderData->goods_price*100));
            $input->setTimeStart(date("YmdHis"));
            $input->setTimeExpire(date("YmdHis", time() + 600));
            $input->setGoodsTag("Reward");
            $input->setNotifyUrl($appinfo['notify_url']);
            trace($input,'wxpaynotify_url');
            $input->setTradeType("JSAPI");
            $input->setOpenid($orderData->pay_code);
            $order = WxPayApi::unifiedOrder($input);

            $jsApiParameters = $tools->getJsApiParameters($order);
            trace($order,'wxpayorder');
            trace($jsApiParameters,'wxpayjsApiParameters');
            trace($orderData,'wxpayorderData');
            return $jsApiParameters;
        }
    }


    /**
     * 微信小程序支付申请退款获取支付参数方法
     * 在微信小程序前端需要进行微信支付的地方通过request接口调用https://www.huaict.com/mpbase/wxapp/wxpay/mp_id/公众号表mp_id字段/id/
     * id参数带上order表的order_id参数请求此地址即可。
     * @return json
     */
    public function wxrefund($id)
    {
        $orderData = OrderGoods::get($id);
        trace($orderData,'wxpayorder订单数据');
        $mp_id = $params['mp_id'] = $map['mp_id'] = get_mpid();
        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        trace($mp_id,'info');
        //        $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        $cfg = array(
            'APPID'     => $appinfo['appid'],
            'MCHID'     => $appinfo['mchid'],
            'KEY'       => $appinfo['mchkey'],
            'APPSECRET' => $appinfo['secret'],
            'NOTIFY_URL' => $appinfo['notify_url'],
        );
        WxPayConfig::setConfig($cfg);

        if (isset($id) && $id != 0) {
            //获取用户openid
            $tools = new JsApiPay();
            trace($tools,'wxpayJsApiPay');
            $total = $orderData->order_total*100;
            $price = $orderData->sum*100;
            //统一下单
            $input = new WxPayRefund();
            $input->setTransactionId($orderData->order_sn);
//            $input->setOutTradeNo($orderData->order_sn);
            $input->setOutRefundNo($appinfo['mchid'].time());
            $input->setTotalFee(intval($total));
            $input->setRefundFee(intval($price));
            $input->setOpUserId($appinfo['mchid']);//操作员

            $order = WxPayApi::refund($input);
            trace($order,'wxpayorder退款');

            if ($order['return_code'] == 'SUCCESS' && $order['result_code'] == 'SUCCESS'){
                $msg = '退款成功';
                OrderGoods::where('id',$id)->setField(['is_pay'=>5,'rec_time'=>date('Y-m-d H:i:s',time())]);

                //规格
                $spec = db('SpecGoodsPrice')->where('goods_id',$orderData->goods_id)->find();
                $spec_id = $spec['spec_id'];
                if ($spec){

                    switch ($orderData->spec_key_name){
                        case $spec['name1']:
                            $spec['store1'] += $orderData->goods_num;
                            break;
                        case $spec['name2']:
                            $spec['store2'] += $orderData->goods_num;
                            break;
                        case $spec['name3']:
                            $spec['store3'] += $orderData->goods_num;
                            break;
                    }
                    db('ShopGoods')->where('id',$orderData->goods_id)->setDec('sales_sum',$orderData->goods_num);
                    db('SpecGoodsPrice')->where('spec_id',$spec_id)->update($spec);
                }

            }elseif (isset($order['err_code']) && $order['err_code'] == 'ERROR'){
                $msg = $order['err_code_des'];
            }else{
                $msg = '退款失败';
            }
            return $msg;
        }
    }

}