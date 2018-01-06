<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\Order as OrderModel;
use app\common\model\order;

class Pay extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {

    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = input('post.');
        foreach( $data as $k=>$v) {           //整理参数
            if('' == $v) unset($data[$k]);
        }
        $data['mp_id'] = get_mpid();
        trace($data['mp_id'],'mpid print');
        $data['buyer_openid'] = get_openid();
        $ret = model('order')->add_or_edit_order($data);
//        $mpid = get_mpid();
//        $openid = get_openid();
//        echo $ret;
        if ($ret){
            $this->assign('price',$data['product_img']);
            $this->redirect("https://www.huaict.com/mpbase/weixin/wxpayjsapi/mp_id/919f95036e68433c72749421307dc99d/id/$ret");

        }else{
            $this->assign('price','');
        }
        return json($ret);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
