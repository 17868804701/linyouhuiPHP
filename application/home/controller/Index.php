<?php
// +----------------------------------------------------------------------
// | UCToo [ Universal Convergence Technology ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://uctoo.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Patrick <contact@uctoo.com>
// +----------------------------------------------------------------------
/**
 *	PC 端门户前台控制器
 *  @version 1.0
 */
namespace app\home\controller;

use think\Controller;
use app\common\model\Config;
use app\common\model\Channel;
use think\Lang;
use app\common\model\Order;
use com\TPWechat;

class Index extends Controller
{
    protected $weObj;          //自动注入的wechat SDK实例,用于管理公众号，自定义微信会员卡、优惠券、运营人员与微会员互动等场景

    //TP5 的架构方法绑定（属性注入）的对象
    public function __construct(TPWechat $weObj)
    {
        $this->weObj = $weObj;
        parent::__construct();
    }

    //系统首页
    public function index()
    {
        $show_blocks = get_kanban_config('BLOCK', 'enable', array(), 'Home');

        $this->assign('showBlocks', $show_blocks);


        $enter = modC('ENTER_URL', '', 'Home');
        $this->assign('enter', get_nav_url($enter));

        $channel = new Channel;
        $navtree = $channel ->lists(true,true); //获取导航栏树
        trace($navtree,'info');
        $sub_menu['left']= array(array('tab' => 'home', 'title' => lang('_SQUARE_'), 'href' =>  url('index'))//,array('tab'=>'rank','title'=>'排行','href'=>url('rank'))
        );

        $this->assign('navtree', $navtree);
        $this->assign('sub_menu', $sub_menu);
        $this->assign('current', 'home');

        $indexType=modC('HOME_INDEX_TYPE','index','Home');
        if($indexType=='index'){
            return $this->fetch('index');
        }
        if($indexType=='login'){
            if(!is_login()){
                redirect(url('Ucenter/Member/login'));
            }
        }
        hook('homeIndex');
        $default_url = config('DEFUALT_HOME_URL');//获得配置，如果为空则显示聚合，否则跳转
        if ($default_url != ''&&strtolower($default_url)!='home/index/index') {
            redirect(get_nav_url($default_url));
        }

        return $this->fetch('index');
    }

    public function donateqrcode(){
        $mp = model('MemberPublic');
        $appinfo = $mp->get(1);        //将id为1的公众号信息输出到页面
        $mp_id = $appinfo['mp_id'];
        $donateUrl = url('home/index/donate',['mp_id'=>$mp_id],false,true);
        trace($donateUrl,'donateUrl');
        return getUrlQRCode($donateUrl);
    }

    protected function _initialize()
    {
        //自动加载语言文件
        $langSet = Lang::detect();
        Lang::load(APP_PATH . 'home'.DS.'lang'.DS.$langSet.'.php');
        Lang::load(APP_PATH . 'weibo'.DS.'lang'.DS.$langSet.'.php');
        /*读取站点配置*/
        $config = model('Config')->lists();
        config($config); //添加配置

        if (!config('WEB_SITE_CLOSE')) {
            $this->error(lang('_ERROR_WEBSITE_CLOSED_'));
        }
    }

    public function search()
    {
        $keywords=input('post.keywords','','text');
        $modules = model('Common/Module')->getAll();
        foreach ($modules as $m) {
            if ($m['is_setup'] == 1 && $m['entry'] != '') {
                if (file_exists(APP_PATH . $m['name'] . '/Widget/Search.php')) {
                    $mod[] = $m['name'];
                }
            }
        }
        $show_search = get_kanban_config('SEARCH', 'enable', $mod, 'Home');

        $this->assign($keywords);
        $this->assign('showBlocks', $show_search);
        return $this->fetch();
    }

    public function test()
    {
        $path = "application\\index\\controller\\index.php";

        // 定义输出文字
        $html = "<p>我是 [path] 文件的index方法</p>";
        echo $html;
        // 调用temphook钩子, 实现钩子业务
        hook('temphook', ['data'=>$html]);

        // 替换path标签
        return str_replace('[path]', $path, $html);
    }

    public function donate()
    {
        $mp = model('MemberPublic');
        $appinfo = $mp->get(1);        //将id为1的公众号信息输出到页面
        trace($appinfo,'donateappinfo');
        $mp_id = $appinfo['mp_id'];
        set_mpid($mp_id);              //设置上下文的公众号信息
        $openid = get_openid();
        trace('get_index','info');
        trace($openid,'info');
        $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        trace('get_ucuser_mid111','info');
        trace($mid,'info');
        $user = get_mid_ucuser($mid);                    //获取本地存储公众号粉丝用户信息
        if(request()->isPost()){                //获取用户输入的金额，添加微信预支付订单信息，并跳转到微信支付收银台页
           $order_total_price = input('order_total_price');
           $oData = new Order();
            $oData->mp_id = $mp_id;
           $oData->mid = $mid;
           $oData->type = 'donate';
           $oData->order_id = $mid.'_'.date("YmdHis");    //order表的order_id字段不能重复
           $oData->order_status = 0;
            $oData->order_total_price = $order_total_price*100;   //界面输入的单位是元，转换为分
            $oData->order_express_price = 0;
            $oData->buyer_openid = $user['openid'];
            $oData->buyer_nick = $user['nickname'];
            $oData->receiver_name = 'UCT';
            $oData->receiver_province = 'guangdong';
            $oData->receiver_city = 'shenzhen';
            $oData->receiver_zone = 'nanshan';
            $oData->receiver_address = '6-310';
            $oData->receiver_mobile = '0755-33942068';  //$user['mobile'];
            $oData->receiver_phone = '0755-33942068';
            $oData->product_id = '10001';
            $oData->product_name = 'UCToo开源捐赠';
            $oData->product_price = 1;
            $oData->product_sku = '10001';
            $oData->product_count = 1;
            $oData->product_img = '';
            $oData->delivery_id = '';
            $oData->delivery_company = '';
            $oData->coupon = '';
            $oData->trans_id = '';
            $oData->module = 'home';
            $oData->addon = '';
            $oData->model = '';
            $oData->aim_id = '';
            $oData->pay_url = '';
            $oData->save();
            $oid = $oData->id;             //获得新增订单的id
            $this->redirect(httpTohttps(url('mpbase/weixin/wxpayjsapi',['mp_id'=>$mp_id,'id'=>$oid],false,true))); //跳转到微信支付收银台
        }else{                         //显示捐赠页

            $this->assign('mp_id',$mp_id);          //将公众号信息输出到页面
            return $this->fetch();
        }
    }
}
