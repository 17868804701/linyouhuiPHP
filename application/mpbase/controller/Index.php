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
use com\wxpay\database\WxPayUnifiedOrder;
use com\wxpay\JsApiPay;
use com\wxpay\NativePay;
use com\wxpay\PayNotifyCallBack;
use think\Log;
use com\wxpay\WxPayApi;
use com\wxpay\WxPayConfig;
use com\TPWechat;
use app\common\model\Order;

/**
 * 前台业务逻辑都放在
 * @var
 */

class Index extends Controller
{
    protected $weObj;          //自动注入的wechat SDK实例,用于管理公众号，自定义微信会员卡、优惠券、运营人员与微会员互动等场景

    //TP5 的架构方法绑定（属性注入）的对象
    public function __construct(TPWechat $weObj)
    {
        $this->weObj = $weObj;
        parent::__construct();
    }

    public function index()
    {
        echo think_ucenter_md5('admin789', UC_AUTH_KEY);

//        return $this->fetch();
    }

}