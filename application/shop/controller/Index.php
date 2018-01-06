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

namespace app\shop\controller;

use think\Controller;
use shop\model\shopOrder as ShopOrderModel;
class Index extends Controller {
    protected $product_cats_model;
    protected $product_model;
    protected $order_model;
    protected $order_logic;
    protected $coupon_model;
    protected $cart_model;
    protected $user_id;
    protected $coupon_logic;
    protected $message_model;
    protected $user_coupon_model;
    protected $user_address_model;
    protected $product_comment_model;

    protected $weObj;
    protected $mp_id;//公众号mp_id
    protected $wx_id;//微信会员id
    function _initialize()
    {
        $this->product_cats_model = model('Shop/ShopProductCats');
        $this->product_model      = model('Shop/ShopProduct');
        $this->order_model        = model('Shop/ShopOrder');
        $this->order_logic        = model('Shop/ShopOrder', 'Logic');
        $this->coupon_logic       = model('Shop/ShopCoupon', 'Logic');
//		$this->theme('mobile');
//		$this->init_shop();
    }



//    public function init_shop()
//    {
//        $configs = db('Config')
//            ->where(array('name' =>array('like', '_' . strtoupper(MODULE_NAME) . '_' . '%')))
//            ->limit(999)->select();
//        $shop    = array();
//        foreach ($configs as $k => $v)
//        {
//            $key                    = str_replace('_' . strtoupper(MODULE_NAME) . '_', '', strtoupper($v['name']));
//            $shop[strtolower($key)] = $v['value'];
//        }
//        $sharedata = array(
//            'title'=>$shop['title'],
//            'desc'=>$shop['notice'],
////			'link'=>'',
//
//            'imgUrl'=>'http://'.$_SERVER['HTTP_HOST'].pic($shop['logo']),
//        );
//        $this->mp_id = $shop['mp_id'];
//        $this->init_wxjs();
//
//        $this->assign('mp_id', $this->mp_id);
//        $this->assign('shop', $shop);
//        $this->assign('sharedata', $sharedata);
//
//    }


//    public function init_user()
//    {
//        $this->user_id = is_login();
//        $this->init_wx();
//        if (!$this->user_id)
//        {
//            $this->error('请在微信中打开');
////			if (IS_POST)
////			{
////				$this->error('请登录', U('shop/index/login'), 1);
////			}
////			else
////			{
////				redirect(U('shop/index/login'));
////			}
//
//        }
//        else if (!is_login())
//        {
//            $Menber_model = new \Admin\Model\MemberModel();
//            $Menber_model->login($this->user_id);
//        }
//    }

    /*
     * 微信登陆
     */
//    public function init_wx()
//    {
//        $isWeixinBrowser = isWeixinBrowser();
//        if (!$isWeixinBrowser)
//        {                           //非微信浏览器返回false，调用此函数必须对false结果进行判断，非微信浏览器不可访问调用的controller
//            return false;
//        }
//        (get_mpid()==-1) && get_mpid($this->mp_id);
//        $this->wx_id = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
//        if ($this->wx_id === false)
//        {
//            //				$this->error('只可在微信中访问');
//            return false;
//        }
//
//        $Ucuser = get_mid_ucuser($this->wx_id);
//        //获取公众号信息
//
//        //注册pc端帐号，
//        if(!$Ucuser['uid'])
//        {
//            $Ucuser['uid'] = UCenterMember()->add(array('status'=>1,'update_time'=>$_SERVER['REQUEST_TIME']));
//            model('Common/Member')->initUserRoleInfo(1,$Ucuser['uid']);
//            model('UserRole')->add(array('uid'=>$Ucuser['uid'],'status'=>1,'role_id'=>1,'step'=>'finsh','init'=>1));
//            model('Ucuser')->save($Ucuser);
//        }
//        $this->user_id = $Ucuser['uid'];
//    }
    /*
     * 初始化微信js
     */
//    public function init_wxjs()
//    {
//        $isWeixinBrowser = isWeixinBrowser();
//        if (!$isWeixinBrowser)
//        {                           //非微信浏览器返回false，调用此函数必须对false结果进行判断，非微信浏览器不可访问调用的controller
//            return false;
//        }
//        $appinfo = get_mpid_appinfo($this->mp_id);
//        $this->assign('appinfo', $appinfo);
//        //初始化options信息
//        $options['appid']          = $appinfo['appid'];
//        $options['appsecret']      = $appinfo['secret'];
//        $options['encodingaeskey'] = $appinfo['encodingaeskey'];
//        $this->weObj               = new TPWechat($options);
//        $this->weObj->checkAuth();
//        $isWeixinBrowser = isWeixinBrowser();
//        if (!$isWeixinBrowser)
//        {                           //非微信浏览器返回false，调用此函数必须对false结果进行判断，非微信浏览器不可访问调用的controller
//            return false;
//        }
//        $js_ticket = $this->weObj->getJsTicket();
//        if (!$js_ticket)
//        {
//            $this->error('获取js_ticket失败！错误码：' . $this->weObj->errCode . ' 错误原因：' . ErrCode::getErrText($this->weObj->errCode));
//        }
//        $url     = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//        $js_sign = $this->weObj->getJsSign($url);
//        $this->assign('js_sign', $js_sign);
//        //默认的分享链接
//        $surl = get_shareurl();
//        if (!empty($surl))
//        {
//            $this->assign('share_url', $surl);
//        }
//    }


    //申请模版
    public function apply(){
        $mp_id = get_mpid();
        $this->assign('mp_id',$mp_id);

        $id = input('id');
        $data = input('post.');
//        if(isset($id)){
//            $list = $this->product_model->where('id',$id)->find();
//        }
//        if (!empty($list['price_int'])){
//            $list['price_int'] = explode(',',$list['price_int']);
//        }else{
//            $list['price_int'] = null;
//        }
        $data['id'] = $id;
        $data['sum'] = $data['number'] * $data['price'];
        $this->assign('list',$data);
        return $this->fetch('/index/index/apply');
    }


    //请求支付
    public function pay(){
        $data = input('post.');
        trace($data,'post-data');
        $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        $user = get_mid_ucuser($mid);                    //获取本地存储公众号粉丝用户信息
        $data['mp_id'] = get_mpid();
        $data['mid'] = $mid;
        $data['type'] = 'trade';
        $data['order_status'] = 0;
        $data['order_id'] = get_ucuser_mid().'_'.date("YmdHis");
        $data['trans_id'] = '';
        $data['buyer_openid'] = $user['openid'];
        $data['buyer_nick'] = $user['nickname'];
        $data['receiver_city'] = 'shenzhen';
        $data['receiver_zone'] = 'nanshan';
        $data['product_sku'] = 10001;
        $data['receiver_phone'] = '0755-33942068';
        $ret = model('order')->add_or_edit_order($data);
        trace($ret,'ret');
//        $mpid = get_mpid();
//        $openid = get_openid();
//        echo $ret;
        if ($ret){
            //$this->assign('price',$data['product_img']);
            $this->redirect(httpTohttps(url('mpbase/weixin/wxpayjsapi',['mp_id'=>$data['mp_id'],'id'=>$ret],false,true))); //跳转到微信支付收银台
        }else{
            $this->assign('price','');
        }
//        return $this->fetch('/index/index/success');
    }
    //详细页
    public function detail(){
        $mp_id = get_mpid();
        $this->assign('mp_id',$mp_id);

        $id = input('id');
        if(isset($id)){
            $title = $this->product_cats_model->find($id);
            $list = $this->product_model->where('cat_title',$title['title'])->find();
            if($list['image'] != 0){
                $list['image_id'] = $list['image'];
                $list['image'] = get_cover($list['image'],'url');
            }

        }
        if (!empty($list['price_int'])){
            $list['price_int'] = explode(',',$list['price_int']);
        }else{
            $list['price_int'] = null;
        }

        if (!empty($list)){
            $this->assign('list',$list);
        }else{
            $this->assign('list',null);
        }

        trace('detail --------------------------end','info');
        return $this->fetch('/index/index/detail');
    }
    //二级菜单列表也
    public function lists(){
        $parent_id = input('parent_id');
        $id = input('id');

        $mp_id = get_mpid();
        $this->assign('mp_id',$mp_id);
        //将ID付给父ID，检测是否还有下级菜单
        if(empty($parent_id)){
            $parent_id = $id;
        }
        $cat_title = $this->product_cats_model->where('id',$parent_id)->column('title');

        $flag = 1;  //信标，判断输出详细页还是列表页

        //遍历下级菜单目录
        $parent = $this->product_cats_model->where('parent_id',$parent_id)->column('id');
        if (!empty($parent)){
            $parent_sub = $this->product_cats_model->where('parent_id',$parent[0])->column('parent_id');
            if (!empty($parent_sub)){
                $flag = 0;
            }
        }else{
            $parent_id ='';
        }


        //点击一级菜单，显示二级菜单列表
        if (isset($parent_id)){
            $list = $this->product_cats_model->where(array('parent_id'=>$parent_id,'status'=>0))->select();
            $banner = $this->product_cats_model->where('id',$parent_id)->column('banner');
            if($banner[0] != 0){
                $banner = get_cover($banner[0],'url');
                $this->assign('banner',$banner);
            }
            foreach ($list as $row){
                $row['image_id'] = $row['image'];
                $row['image'] = get_cover($row['image'],'url');
            }
            $this->assign('flag',$flag);
            $this->assign('cat_title',$cat_title[0]);
            $this->assign('list',$list);

            return $this->fetch('/index/index/lists');
        }

        //二级菜单直接显示内容
        if(isset($id)){
            //echo 'id'.$id;
            $title = $this->product_cats_model->find($id);
            $list = $this->product_model->where('cat_title',$title['title'])->select();
//            $list = $this->product_cats_model->where('parent_id',$id)->select();
//            var_dump($title);
            trace($list,'list');
            $banner = $this->product_cats_model->where('id',$id)->column('banner');
            if($banner[0] != 0){
                $banner = get_cover($banner[0],'url');
                $this->assign('banner',$banner);
            }

            foreach ($list as $row){
                $row['image_id'] = $row['image'];
                $row['image'] = get_cover($row['image'],'url');
            }
            $this->assign('flag',1);
            $this->assign('cat_title',$cat_title[0]);
            $this->assign('list',$list);
            return $this->fetch('/index/index/lists');
        }


    }



    //个人中心
    public function person(){
        return $this->fetch('/index/index/personal');
    }
    //订单详情
    public function orderInfo(){
        $id = input('id');
        $order = model('order')->find($id);
        $this->assign('order',$order);
        return $this->fetch('/index/index/orderInfo');
    }
    //订单状态
    public function order(){
        $mp_id = get_mpid();
        $openid= get_openid();
        $map['buyer_openid'] = $openid;
        $map['mp_id'] = $mp_id;
        $orders = model('order')->where($map)->order('id desc')->select();
        $this->assign('order',$orders);
        return $this->fetch('/index/index/order');
    }

    public function index()
    {
        $mp_id = get_mpid();
        $this->assign('mp_id',$mp_id);

        $option['parent_id'] = input('parent_id',0,'intval');
        if(!empty($option['parent_id']))
        {
            $parent_cat  = $this->product_cats_model->get_product_cat_by_id($option['parent_id']);
        }
        if(input('all')) $option = array();
        $option['page'] = 1;//当前页
        $option['r']  =  10;
        //获取幻灯片
        $slides = db('shop_slides')->where('status',0)->order('sort desc')->limit(3)->select();
        foreach ($slides as &$s){
            $s['image'] = get_cover($s['image'],'url');
        }
        $this->assign('slides',$slides);
        //获取顶级菜单
        $cats = $this->product_cats_model->where(array('parent_id'=>0,'status'=>0))->order('sort desc, create_time')->paginate($option['r'], true,
            ['page'=>$option['page']]);

        //获取图片路径
        foreach ($cats as $row){
            $row['image'] = get_cover($row['image'],'url');
        }
        //获取子级菜单
        foreach ($cats as &$one ){
            $tmp = $this->product_cats_model->where('parent_id',$one['id'])->order('sort desc, create_time')
                ->paginate(3, true,['page'=>1]);
            $tmp = $tmp->toArray();
            $one['sub'] = $tmp['data'];
        }
        //dump($cats->toArray());

        $page = $cats->render();//分页
        $count = $this->product_cats_model->where('status',0)->count();
        $totalCount = $count;
        $this->assign('list',$cats->toArray());//输出数据列表
        $this->assign('page',$page);
        return $this->fetch('/index/index/index');

    }

    //关于我们
    function about(){
        $ret = $this->product_model->get_about_by_id(1);
        $this->assign('list',$ret);
        return $this->fetch('/index/index/about');
    }


    public function product()
    {
        $id      = input('id', '', 'intval');
        $product = $this->product_model->get_product_by_id($id);
        $this->assign('product', $product);
        $sharedata = array(
            'title'=>$product['title'],
//			'desc'=>,
            'imgUrl'=>'http://'.$_SERVER['HTTP_HOST'].pic($product['main_img']),
        );
        $this->assign('sharedata', $sharedata);
        $this->theme('mobile')->display();
    }



    public function orders()
    {
        $this->init_user();
        $option['status'] = input('status',0,'intval');
        $option['page'] = input('1','','intval');
        $option['r'] = input('10','','intval');
        $option['user_id'] = $this->user_id;
        if(IS_POST)
        {
            $order_list = $this->order_model->get_order_list($option);
            $order_list['list'] = empty($order_list['list'])?array(): $order_list['list'];
            array_walk($order_list['list'],function(&$a)
            {
                empty($a['products']) ||
                array_walk($a['products'],function(&$b)
                {
                    $b['main_img'] = (empty($b['main_img'])?'':pic($b['main_img']));
                });
            });
            $this->success($order_list);
        }
        ELSE
        {
            $this->assign('option', $option);
            $this->display();
        }


    }




    /**
     *
     * jsApi微信支付示例
     * 注意：
     * 1、微信支付授权目录配置如下  http://test.uctoo.com/addon/Wxpay/Index/jsApiPay/mp_id/
     * 2、支付页面地址需带mp_id参数
     * 3、管理后台-基础设置-公众号管理，微信支付必须配置的参数都需填写正确
     * @param array $mp_id 公众号在系统中的ID
     * @return 将微信支付需要的参数写入支付页面，显示支付页面
     *
     *
     *
     *  参数 mp_id 微信公众号id
     *      order_id 订单id
     */

    public function jsApiPay(){
        $this->init_user();
        empty($this->mp_id) && $this->error('支付暂不可使用');//没配置收款公众号
        $info     = get_mpid_appinfo($this->mp_id);
        $mid = get_ucuser_mid();                         //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
//		if($mid === false){
//			$this->error('只可在微信中访问');
//		}
        $user = get_mid_ucuser($mid);                    //获取本地存储公众号粉丝用户信息
        $this->assign('user', $user);

        $surl = get_shareurl();
        if(!empty($surl)){
            $this->assign ( 'share_url', $surl );
        }

        $order_id = input('order_id',false,'intval');
        if(empty($order_id)) $this->error('缺少订单号'); //没订单号

        $odata = $this->order_logic->BeforePayOrder($order_id,$this->user_id,$this->mp_id);
        if(!$odata)
        {
            $this->error('订单初始化失败,'.$this->order_logic->error_str);
        }
        if (!($jsApiParameters = S('shop_order_' . $order_id . '_jsApiParameters')))
        {
            //获取公众号信息，jsApiPay初始化参数
            $cfg = array(
                'APPID'      => $info['appid'],
                'MCHID'      => $info['mchid'],
                'KEY'        => $info['mchkey'],
                'APPSECRET'  => $info['secret'],
                'NOTIFY_URL' => $info['notify_url'],
            );
            WxPayConfig::setConfig($cfg);

            //①、初始化JsApiPay
            $tools    = new JsApiPay();
            $wxpayapi = new WxPayApi();
            //检查订单状态 微信回调延迟或出错时 保证订单状态
            $inputs = new WxPayOrderQuery();
            $inputs->SetOut_trade_no($odata['order_id']);
            $result = $wxpayapi->orderQuery($inputs);
            if(array_key_exists("return_code", $result)
                && array_key_exists("result_code", $result)
                && array_key_exists("trade_state", $result)
                && $result["return_code"] == "SUCCESS"
                && $result["result_code"] == "SUCCESS"
                && $result["trade_state"] == "SUCCESS"
            )
            {
                $this->order_logic->AfterPayOrder($result,$odata);
                redirect(U('shop/index/orderdetail',array('id'=>$order_id)));
            }
            //②、统一下单
            $input = new WxPayUnifiedOrder();           //这里带参数初始化了WxPayDataBase
            $input->SetBody($odata['product_name']);
            $input->SetAttach($odata['product_sku']);
            $input->SetOut_trade_no($odata['order_id']);
            $input->SetTotal_fee($odata['order_total_price']);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetTrade_type("JSAPI");
            $input->SetOpenid($user['openid']);

            $order = $wxpayapi->unifiedOrder($input);
            $jsApiParameters = $tools->GetJsApiParameters($order);
//			$editAddress = $tools->GetEditAddressParameters();
//			//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
            S('shop_order_' . $order_id . '_jsApiParameters', $jsApiParameters, 575);//设置缓存 缓存过期时间 稍微比微信支付过期短点
        }
        $this->assign ( 'order', $odata );
        $this->assign ( 'jsApiParameters', $jsApiParameters );
//		$this->assign ( 'editAddress', $editAddress );
        $this->display ();
    }

    /*
 * 取扫描支付二维码
 */
    public function nativepay()
    {
        $this->init_user();
        empty($this->mp_id) && $this->error('支付暂不可使用');//没配置收款公众号
        $info     = get_mpid_appinfo($this->mp_id);
        $order_id = input('order_id', '', 'intval');
        empty($order_id) && $this->error('缺少订单号');

        $odata = $this->order_logic->BeforePayOrder($order_id,$this->user_id,$this->mp_id);
        if(!$odata)
        {
            $this->error($this->order_logic->error_str);
        }

        if (!($result["code_url"] = S('shop_order_' . $order_id . '_code_url')))
        {
            //获取公众号信息，jsApiPay初始化参数
            $cfg = array(
                'APPID'      => $info['appid'],
                'MCHID'      => $info['mchid'],
                'KEY'        => $info['mchkey'],
                'APPSECRET'  => $info['secret'],
                'NOTIFY_URL' => $info['notify_url'],
            );
            WxPayConfig::setConfig($cfg);
            $notify = new NativePay();
            $input  = new WxPayUnifiedOrder();
            $input->SetBody($odata['product_name']);
            $input->SetOut_trade_no($odata['order_id']);
            $input->SetTotal_fee($odata['product_price']);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetTrade_type("NATIVE");
            $input->SetProduct_id($odata['product_id']);
            $result = $notify->GetPayUrl($input);
            S('shop_order_' . $order_id . '_code_url',$result["code_url"],575);
        }
        $this->assign ( 'order', $odata );
        $this->assign ( 'isWeixinBrowser', isWeixinBrowser() );
        $this->assign ( 'code_url', $result["code_url"] );
        $this->display ();
    }


    public function preview_delivery()
    {

//		var_dump(__file__.' line:'.__line__,$_REQUEST);exit;
        $address = array(
            'province' => input('province', '','text'),
            'city'     => input('city', '','text'),
            'town'     => input('town', '','text'),
        );

        $products = input('products','');
        if(empty($products))
        {
            $products = array(
                array(
                    'id'   => input('id','','intval'), //商品id
                    'count' => input('quantity', 1,'intval'), //商品数目
                ));
        }
        else
        {
            is_array($products) || $this->error();
            foreach ($products as $k => &$p)
            {
                ($p['id'] = input('data.id','','intval',$p)) || $this->error(1);
                ($p['quantity'] = input('data.quantity','','intval',$p)) || $this->error(2);
                $products[$k]['count'] = $products[$k]['quantity'];
            }
        }
        $ret = $this->order_logic->precalc_delivery($products, $address);
        if($ret)
        {
            $this->success($ret);
        }
        else
        {
            $this->error();
        }

    }


    public function test()
    {
        $ret =D()->query('select * from a');
        $ret2 = array();
        foreach($ret as $l=>$a)
        {
            $ret2[$l]['id'] = $a['id'];
            $ret2[$l]['name'] = $a['name'];
            $ret2[$l]['type'] = $a['type'];
            $ret2[$l]['center'] =$a['4'].','.$a['5'];
        }
        $this->ajaxreturn($ret2);
        //		var_dump(__file__.' line:'.__line__,$ret2);exit;
    }



}