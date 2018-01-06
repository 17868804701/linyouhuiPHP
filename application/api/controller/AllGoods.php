<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\shop\model\Shop;

class AllGoods extends Controller
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
        //检测为群主还是用户
        $owner = db('shop')->where(array('id'=>$shop,'shop_owner_id'=>$uid))->find();
        if ($owner){
            $list =  model('shop_goods')->where('shop_id='.$shop.' and is_on_sale=0')->order('sort','desc')->order('id','desc')->select();
              foreach ($list as &$item){
                $item['goods_img'] = get_cover($item['image'],'url');
            }
            return json($list);
        }

        return 0;
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

        //发送消息
        $goods = db('ShopGoods')->find($param['id']);
        $data = array();
        $data['owner_id'] = $goods['user_id']; //缓过来,用户ID
        $data['shop_id'] = $goods['shop_id'];
        $data['user_id'] = input('user_id');
        $data['shop_name'] = $goods['shop_name'];
        $data['create_time'] = time();
        $data['type'] = 3;//普通小学




        //审核商品
        if ($param['status'] == 1){
            $status = 0;//上架
            $data['brief'] = '群主已将商品:'.$goods['goods_name'].' 上架';
        }else{
            $status = 1;//下架
            $data['brief'] = '群主已将商品:'.$goods['goods_name'].' 下架';
            $OrderGoods =  model('OrderGoods');
            $order_id = $OrderGoods->where(array('goods_id'=>$param['id'],'is_pay'=>0))->column('id,order_id,sum');
            trace($order_id,'返回菜单');
            foreach ($order_id as $o){
//                $order = model('ShopOrder')->where("")
                $count = $OrderGoods->where("order_id",$o['order_id'])->where('is_pay',0)->count();
                if ($count == 1){
                    model('ShopOrder')->destroy($o['order_id']);
                }else{
                    model('ShopOrder')->where('id',$o['order_id'])->setDec('goods_price',$o['sum']);
                }

                $OrderGoods->where(array('goods_id'=>$param['id'],'is_pay'=>0))->delete();
            }
        }
        unset($param['status']);
        unset($param['user_id']);
        db('ShopMessages')->insert($data);
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
        unset($param['id']);
        trace($param,'更细');
        $param['brief'] = ' 申请上架商品:';
        $param['type'] = 1;//商品
        $param['create_time'] = time();
        $ret = db('ShopMessages')->insert($param);
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
