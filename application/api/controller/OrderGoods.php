<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
//use app\common\model\ShopOrder as ShopOrder;

class OrderGoods extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $param = input('param.');
        foreach ($param as $k => $value){
            if ($value == '') unset($param[$k]);
        }
        if (!empty($param)){
            $ret = model('shop_order')->order_lists($param);
        }else{
            $ret = model('shop_order')->selete();
        }
        return json($ret);
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
        $data = input('param.');
        foreach( $data as $k=>$v) {           //整理参数
            if('' == $v) unset($data[$k]);
        }
        trace($data,'提交订单商品数据');

        $spec = db('SpecGoodsPrice')->where('goods_id',$data['goods_id'])->find();
        $spec_id = $spec['spec_id'];
        $spevArr = array('status'=>false,'data'=>'数量不足，提交失败');
        if ($spec){

            switch ($data['spec_key_name']){
                case $spec['name1']:
//                    $spec['store1'] -= $data['goods_num'];
                    if ($spec['store1'] <0){
                        return json($spevArr);
                    }
                    break;
                case $spec['name2']:
//                    $spec['store2'] -= $data['goods_num'];
                    if ($spec['store2'] <0){
                        return json($spevArr);
                    }
                    break;
                case $spec['name3']:
//                    $spec['store3'] -= $data['goods_num'];
                    if ($spec['store3'] <0){
                        return json($spevArr);
                    }
                    break;
            }
//            db('ShopGoods')->where('id',$data['goods_id'])->setInc('sales_sum',$data['goods_num']);
//            db('SpecGoodsPrice')->where('spec_id',$spec_id)->update($spec);
        }
        $ret = model("order_goods")->posts($data);
        if ($ret==1){
            return json(array('status'=>true,'data'=>'提交成功'));
        }else{
            return json(array('status'=>false,'data'=>'提交失败'));
        }

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
