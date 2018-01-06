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

use think\Controller;
use think\Request;
use app\common\model\ShopOrder;
use com\TPWechat;
use com\wxpay\database\WxPayUnifiedOrder;
use com\wxpay\JsApiPay;
use com\wxpay\NativePay;
use com\wxpay\WxPayApi;
use com\wxpay\WxPayConfig;
use app\mpbase\controller\PayNotifyCallBack;
use app\common\model\Order;
use think\Log;

/**
 * 微信交互控制器，中控服务器
 * 主要获取和反馈微信平台的数据，分析用户交互和系统消息分发。
 */
class Weixin extends Controller {

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
     * 微信消息接口入口
     * 所有发送到微信的消息都会推送到该操作
     * 所以，微信公众平台后台填写的api地址则为该操作的访问地址
     * 在mp.weixin.qq.com 开发者中心配置的 URL(服务器地址)  http://域名/index.php/mpbase/weixin/index/mp_id/member_public表的mp_id.html
     */
	public function index($mp_id = '') {
        trace($mp_id,'app_debugmp_id');
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

        hook('init_ucuser',$params);   //执行addons/ucuser/Ucuser/init_ucuser的方法,初始化公众号粉丝信息
        trace($params,'after_init_ucuser');
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
                        $where['statu']= 0;
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
//                        $where['keywork']=array('like', '%' . $data['Content'] . '%');
                        $where['keywork']=array('like', '%' . $event['key'] . '%');
                        $where['mtype']= 3;
                        $where['statu']= 0;
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
                $where['statu']= 0;
                $where['mp_id']=get_mpid();
                $model_re = model('Mpbase/ReplayMessages');
                $data_re=$model_re->order('time desc')->where($where)->find();
//              关键字匹配失败进入自动回复
                if(!$data_re){
                    unset($where);
                    $where['mtype']= 2;
                    $where['statu']= 0;
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
     * 微信支付收银台方法
     * 在公众号后台mp.weixin.qq.com支付授权目录配置https://www.huaict.com/mpbase/weixin/wxjsapipay/mp_id/公众号表mp_id字段/id/
     * 需要微信支付功能的地方带上order表的id参数跳转到此地址即可。
     * @return mixed
     */
    public function wxpayjsapi($id)
    {
        $orderData = Order::get(['order_id'=>$id]);
        trace($orderData,'order');
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
            trace($tools,'JsApiPay');
            //统一下单
            $input = new WxPayUnifiedOrder();
            $input->setBody($orderData->product_name);
            $input->setAttach($orderData->product_sku);
            $input->setOutTradeNo($orderData->order_id);  //建议默认的预支付交易单商户订单号由date("YmdHis").'_'.order_id组成
            $input->setTotalFee($orderData->order_total_price);
            $input->setTimeStart(date("YmdHis"));
            $input->setTimeExpire(date("YmdHis", time() + 600));
            $input->setGoodsTag("Reward");
            $input->setNotifyUrl($appinfo['notify_url']);
            trace($input,'notify_url');
            $input->setTradeType("JSAPI");
            $input->setOpenid($openid);
            $order = WxPayApi::unifiedOrder($input);


            $jsApiParameters = $tools->getJsApiParameters($order);
            trace($order,'order');
            trace($jsApiParameters,'jsApiParameters');
            trace($orderData,'orderData');
            $this->assign('order', $order);
            $this->assign('orderData', $orderData);
            $this->assign('jsApiParameters', $jsApiParameters);
            return $this->fetch();
        }
    }

    /**
     * 使用微信支付SDK生成支付用的二维码
     * 需要显示扫码支付二维码的地方带上order表的id参数 <img src="{:url('mpbase/weixin/wxpayqrcode',['mp_id'=>$mp_id,'id'=>$id])}">
     * 用户十分钟未支付，则微信支付预支付订单过期需
     * @param $id
     */
    public function wxpayqrcode($id)
    {
        $orderData = ShopOrder::get($id);
        $this->assign('orderData', $orderData);
        if (!isset($orderData)) $this->error('查询不到正确的订单信息');
        $mp_id = $params['mp_id'] = $map['mp_id'] = get_mpid();
        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        $cfg = array(
            'APPID'     => $appinfo['appid'],
            'MCHID'     => $appinfo['mchid'],
            'KEY'       => $appinfo['mchkey'],
            'APPSECRET' => $appinfo['secret'],
            'NOTIFY_URL' => $appinfo['notify_url'],
        );
        WxPayConfig::setConfig($cfg);
        //判断是否已经存在订单 url，如果已经存在且未超过2小时就使用旧的，否则生成新的
        $interval = time() - $orderData->add_time;
        trace($interval,'interval');
        if (isset($orderData->pay_url) && $orderData->pay_url != '' && $interval < 600) {
            $url = $orderData->pay_url;
            trace($url,'pay_url111');
        } else {
            $notify = new NativePay();
            $input = new WxPayUnifiedOrder();
            $input->setBody($orderData->pay_name);
            $input->setAttach($orderData->user_id);
            $input->setOutTradeNo($orderData->order_sn);  //建议默认的预支付交易单商户订单号由date("YmdHis").'_'.order_id组成
            $input->setTotalFee($orderData->goods_price);
            $input->setTimeStart(date("YmdHis"));
            $input->setTimeExpire(date("YmdHis", time() + 600));
            $input->setGoodsTag("QRCode");
            $input->setNotifyUrl($appinfo['notify_url']);

            $input->setTradeType("NATIVE");
            $input->setProductId($id);
            $result = $notify->getPayUrl($input);
            trace($result,'result');
            $url = $result["code_url"];
            trace($url,'code_url');
            //保存订单标识
            $orderData->pay_url = $url;
            $orderData->save();
        }

        //生成二维码
        return getUrlQRCode($url);
    }
    /**
     * 微信支付二维码收银台方法
     * 需要微信扫码支付功能的地方带上order表的id参数跳转到此地址即可。https://www.huaict.com/mpbase/weixin/wxpayqc/mp_id/公众号表mp_id字段/id/
     * @return mixed
     */
    public function wxpayqc()
    {
        $mp_id = input('mp_id');
        $id = input('id');
        $orderData = Order::get($id);
        if (!isset($orderData)) $this->error('查询不到正确的订单信息');
        $this->assign('orderData', $orderData);
        $this->assign('mp_id', $mp_id);
        $this->assign('id', $id);
        return $this->fetch();
    }

    /**
     * 异步接收订单返回信息，订单成功付款后，处理订单状态并批量生成用户的二维码
     * @param int $id 订单编号
     */
	public function notify(){
		$rsv_data = request()->getInput();
		$result   = xmlToArray($rsv_data);
        trace($result,'notifyresult');
        $map["appid"] = $result["appid"];
        $info         = model('member_public')->where($map)->find();
        $mp_id = $info['mp_id'];
        addWeixinLog ( $rsv_data, '',$mp_id, 0,$result['appid'],$result['openid'],'wxpay_notify');
        //获取公众号信息，jsApiPay初始化参数
        $cfg = array(
            'APPID'      => $info['appid'],
            'MCHID'      => $info['mchid'],
            'KEY'        => $info['mchkey'],
            'APPSECRET'  => $info['secret'],
            'NOTIFY_URL' => $info['notify_url'],
        );
        WxPayConfig::setConfig($cfg);

		//回复公众平台支付结果
		$notify = new PayNotifyCallBack();
		$notify->Handle(false);
	}

	/*
	 * 查询微信支付的订单
	 * 注意 这里未做权限判断
	 */
	public function orderquery()
	{
		$id = input('id','','intval');
		$order  = model("Order");
		if(empty($id)||!($odata = $order->where('id = '. $id )->find()))
		{
			$this->error('该支付记录不存在');
		}
		$map["mp_id"] = $odata["mp_id"];
		$info         = model('member_public')->where($map)->find();
		//获取公众号信息，jsApiPay初始化参数
		$cfg = array(
			'APPID'      => $info['appid'],
			'MCHID'      => $info['mchid'],
			'KEY'        => $info['mchkey'],
			'APPSECRET'  => $info['secret'],
			'NOTIFY_URL' => $info['notify_url'],
		);
		WxPayConfig::setConfig($cfg);
		$input = new WxPayOrderQuery();
		$input->SetOut_trade_no($odata['order_id']);
		$result = WxPayApi::orderQuery($input);
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& array_key_exists("trade_state", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS"
			&& $result["trade_state"] == "SUCCESS")
		{
			// $odata['module'] = Shop 则在D('ShopOrder','Logic')->AfterPayOrder() 内处理后续逻辑
			$class = parse_res_name($odata['module'].'/'.$odata['module'].'Order','Logic');
			if(class_exists($class) &&
				method_exists($class,'AfterPayOrder'))
			{
				$m = new $class();
				$m->AfterPayOrder($result,$odata);
			}
			$this->success('已支付');
		}
		$this->error((empty($result['trade_state_desc'])?'未支付':$result['trade_state_desc']));
	}

    /**
     * @return mixed
     * 发红包
     */

    function sendRedCash($id) {
        $package = array();

        //公众号信息
        $mp_id = $params['mp_id'] = $map['mp_id'] = get_mpid();
        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        $cfg = array(
            'APPID'     => $appinfo['appid'],
            'MCHID'     => $appinfo['mchid'],
            'KEY'       => $appinfo['mchkey'],
            'APPSECRET' => $appinfo['secret'],
            'NOTIFY_URL' => $appinfo['notify_url'],
        );


        $package['nonce_str'] = $this->weObj->getNoncestr();
        $package['mch_billno'] = $appinfo['mchid'].date('YmdHis').rand(1000, 9999);
        $package['mch_id'] = $appinfo['mchid'];
        $package['wxappid'] = $appinfo['appid'];
        $package['nick_name'] =$this->send_name;
        $package['send_name'] = $this->send_name ;
        $package['re_openid'] = $this->openid;
        $package['total_amount'] = $this->amount;
        $package['min_value'] = $this->amount;
        $package['max_value'] = $this->amount;
        $package['total_num'] = 1;
        $package['wishing'] = $this->act_name;
        $package['client_ip'] = get_client_ip();
        $package['act_name'] = $this->act_name;
        $package['remark'] = $this->act_name;


        ksort($package, SORT_STRING);
        $strSign = '';
        foreach($package as $key => $v) {
            $strSign .= "{$key}={$v}&";
        }
        $strSign .= "key={$this->key}";
        $package['sign'] = strtoupper(md5($strSign));

//        $xml = $this->arrayToXml($package);
        $xml = toXml($package);

        $certs = array(
            'SSLCERT' => getcwd().'\Application\Common\hongbao\apiclient_cert.pem',
            'SSLKEY' => getcwd().'\Application\Common\hongbao\apiclient_key.pem',
            'CAINFO' => getcwd().'\Application\Common\hongbao\rootca.pem',
        );

        $response = $this->http_request($this->url, $xml, $certs, 'post');

        $responseObj = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
        $aMsg = (array)$responseObj;

        if (isset($aMsg['err_code'])) {
            $db_data['err_code'] = $aMsg['err_code'];
            $db_data['err_code_des'] = $aMsg['err_code_des'];
        }else {
            $db_data['err_code'] = 'SUCCESS';
            $db_data['err_code_des'] = '发送成功，领取红包';
        }
        $db_data['return_msg'] = serialize($aMsg);

        return $db_data;
    }
}