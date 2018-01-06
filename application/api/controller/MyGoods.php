<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\shop\model\Shop;

class MyGoods extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $uid = input('uid');
        $shop = input('shop');
        $status = input('status');
        //检测为群主还是用户
        if ($status != '') {
            $list = model('shop_goods')->where(array('user_id' => $uid, 'is_on_sale' => $status, 'shop_id' => $shop))->order('sort', 'desc')->order('on_time', 'desc')->select();
            foreach ($list as &$item){
                $item['goods_img'] = get_cover($item['image'],'url');
            }
        }else{
            $list = model('shop_goods')->where(array('user_id' => $uid,  'shop_id' => $shop))->order('sort', 'desc')->order('on_time', 'desc')->select();
            foreach ($list as &$item){

                $item['goods_img'] = get_cover($item['image'],'url');
                $spec = db('SpecGoodsPrice')->where('goods_id',$item['id'])->find();
                $specArr = array();
                if ($spec){
                    unset($spec['goods_id']);
                    unset($spec['spec_id']);
                    foreach ($spec as $k => $value){    //过滤规格
                        if (!$value || $value == '0.00') unset($spec[$k]);
                        else $specArr[] = $value;
                    }
                    $spec = array_chunk($specArr,3);
                    $item['spec'] = $spec;
                }
            }
        }

        return json($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {

    }

    /**
     * 用来提交表单数据保存到数据库
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //提交数据
        $param = input('post.');

        foreach( $param as $k=>$v) {           //整理参数
            if('' == $v) unset($param[$k]);
        }
        trace($param,'追踪');
        //审核商品
        if ($param['status'] == 1){
            $status = 0;
        }else{
            $status = 1;
            $OrderGoods =  model('OrderGoods');
            $order_id = $OrderGoods->where(array('goods_id'=>$param['id'],'is_pay'=>0))->column('id,order_id,sum');
            foreach ($order_id as $o){
                $count = $OrderGoods->where("order_id",$o['order_id'])->where('is_pay',0)->count();
                if ($count == 1){
                    model('ShopOrder')->destroy($o['order_id']);
                }else{
                    model('ShopOrder')->where('id',$o['order_id'])->setDec('goods_price',$o['sum']);
                }
            }
            $OrderGoods->where(array('goods_id'=>$param['id'],'is_pay'=>0))->delete();
        }
        unset($param['status']);
        unset($param['user_id']);

        $ret = model('shop_goods')->where($param)->update(['is_on_sale'=>$status]);
        trace($ret,'结果');
        if ($ret){
            //发送审核信息
            return  json(array('status'=>1,'msg'=>'成功'));
        }else{
            return  json(array('status'=>0,'msg'=>'失败'));
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
        $param = input('param.');
        foreach( $param as $k=>$v) {           //整理参数
            if('' == $v) unset($param[$k]);
        }
        $ret = db('ShopMessages')->where(array('user_id'=>$param['user_id'],'goods_id'=>$param['goods_id'],'status'=>0,'type'=>1))->find();
        if(!$ret){
            unset($param['id']);
            trace($param,'更细');
            $param['brief'] = ' 申请上架商品:';
            $param['type'] = 1;//商品
            $param['create_time'] = time();
            $ret = db('ShopMessages')->insert($param);
        }

        return $ret;
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $ret = model('shop_goods')->where(['id'=>$id])->setField(['is_on_sale'=>3]);

        if ($ret){
            return  json(array('status'=>1,'msg'=>'删除成功'));
        }else{
            return  json(array('status'=>0,'msg'=>'删除失败'));
        }
    }
}
