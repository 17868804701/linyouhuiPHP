<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\ShopOrder;
use app\common\model\OrderGoods;
use think\Db;


class BuyOrder extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $shop_id = input('shopId');//用户查询店铺所有订单
        $status = input('status',0);//根据订单状态
        $order_id = input('order_id');//查询当前提交订单
        $user_id = input('user_id');

        //查询单个
        if (!empty($order_id)){
            $order = OrderGoods::get($order_id);
            return json($order);
        }
        //查询不同订单
        //0代付款 1 已经付款代发货，2发货。3是退款。4是取货,5退款完成，6取消
        $db_prefix=config('table_prefix');
        switch ($status){
            case 0:
                $order = OrderGoods::where(array('owner_id'=>$user_id,'is_pay'=>0))->order('id','desc')->select();
                break;
            case 1:
                $order = OrderGoods::where(array('owner_id'=>$user_id,'is_pay'=>1,'is_send'=>0))->order('id','desc')->select();
                break;
            case 2:
                $order = OrderGoods::where(array('owner_id'=>$user_id,'is_pay'=>1,'is_send'=>1))->order('id','desc')->select();
                break;
            case 3:
                $order = OrderGoods::where(array('owner_id'=>$user_id,'is_pay'=>3))->order('id','desc')->select();//退款
                break;
            case 4:
                $order = OrderGoods::where(array('owner_id'=>$user_id,'is_pay'=>1,'is_send'=>2))->order('id','desc')->select();
                break;
            case 5:
                $order = Db::table("{$db_prefix}order_goods")->where("owner_id=$user_id and is_comment = 1 and is_pay=1 and is_send =2")->whereOr('owner_id ='.$user_id.' and is_pay =5')->order('id','desc')->select();
                break;
        }
        return json($order);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     *
     * 0代付款 1 已经付款代发货，2发货。3是退款。4是取货,5退款完成，6取消
     *
     */
    public function create()
    {
        $id = input('order_id');
        trace($id,'id');
        $ret = ShopOrder::where(array('id'=>$id))->find();
        $ret['add_time'] = date('Y-m-d H:i:s',$ret['add_time']);
        $ret['goods'] = OrderGoods::where(array("order_id"=>$id,'is_pay'=>0))->order('id','desc')->select();

        return json($ret);
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
        trace($data,'申请退款');

        $this->redirect(url('@mpbase/wxapp/wxrefund',['id'=>$data['id'],'mp_id'=>$data['mp_id']]));

    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $item = OrderGoods::where('id',$id)->find();
        OrderGoods::where('id',$id)->setField(['is_pay'=>1]);

        $data = array();
        $data['user_id'] = $item['owner_id']; //缓过来
        $data['user_name'] = $item['user_name']; //缓过来
        $data['owner_id'] = $item['user_id'];
        $data['goods_id'] = $item['goods_id'];
        $data['goods_name'] = $item['goods_name'];
        $data['create_time'] = time();
        $data['brief'] = '卖家拒绝了商品:'. $item['goods_name'] .'退款申请';
        $data['type'] = 3;//上架
        db('ShopMessages')->insert($data);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $item = OrderGoods::where('id',$id)->find();
        OrderGoods::where('id',$id)->setField(['is_send'=>1]);

        $data = array();
        $data['user_id'] = $item['owner_id']; //缓过来
        $data['user_name'] = $item['user_name']; //缓过来
        $data['owner_id'] = $item['user_id'];
        $data['goods_id'] = $item['goods_id'];
        $data['goods_name'] = $item['goods_name'];
        $data['create_time'] = time();
        $data['brief'] = '商品:'.$item['goods_name'].' 已到达自提点';
        $data['type'] = 3;//上架
        $ret = db('ShopMessages')->insert($data);
        return $ret;
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update($id)
    {
        $data = input('param.');
        if (empty($data['delivery_id'])){
            $data['delivery_id'] = '未填写信息';
        }
        $ret = OrderGoods::where('id',$id)->setField(['is_send'=>1,'delivery_id'=>$data['delivery_id']]);
        $goods = OrderGoods::get($id);
        trace($goods,'订单');

        $data = array();
        $data['user_id'] = $goods->owner_id; //缓过来
        $data['owner_id'] = $goods->user_id;
        $data['goods_id'] = $goods->goods_id;
        $data['goods_name'] = $goods->goods_name;
        $data['create_time'] = time();
        $data['brief'] = '商品:'.$data['goods_name'].'已经发货';
        $data['type'] = 3;//上架
        db('ShopMessages')->insert($data);
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
        $orderid = input('order');
        $goods = OrderGoods::get($id);


        $count = OrderGoods::where("order_id",$orderid)->count();
        if ($count == 1){
            $ret = model('ShopOrder')->destroy($orderid);
        }else{
            $ret = model('ShopOrder')->where('id',$orderid)->setDec('goods_price',$goods->sum);
        }
        OrderGoods::where('id',$id)->delete();

        //发送消息
            $data = array();
            $data['user_id'] = $goods->owner_id; //缓过来
            $data['shop_id'] = 0;//$goods->shop_id;
            $data['owner_id'] = $goods->user_id;
            $data['goods_id'] = $goods->goods_id;
            $data['goods_name'] = $goods->goods_name;
            $data['create_time'] = time();
            $data['brief'] = '商品:'.$data['goods_name'].'订单已被取消';
            $data['type'] = 3;//上架
            $ret = db('ShopMessages')->insert($data);
//            trace($val,'商品');


        return $ret;
    }
}
