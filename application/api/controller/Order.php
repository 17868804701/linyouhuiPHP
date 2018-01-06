<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\ShopOrder;
use app\common\model\OrderGoods;
use app\common\model\Message;


class Order extends Controller
{
    /**
     * 显示资源列表
     *order_status 0 代付款 1付款，is_pay =1 && is_send=1待取货 2评价  3退款 4完成
     * is_sned =2取货  pay=3退款
     * 0代付款 1 已经付款代发货，2代取货。3是退款。4已取货待评价,5退款什么都完成，6取消
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $id = input('id');//用户查询我d所有订单
        $status = input('status',0);//根据订单状态
        $order_id = input('order_id');//查询当前提交订单
        if (!empty($id)){
            switch ($status){
                case 0:
                    $ret = ShopOrder::where(array('user_id'=>$id,'order_status'=>$status))->order('add_time','desc')->select();
                    foreach ($ret as $val){
                        $val['goods'] = OrderGoods::where(array("order_id"=>$val['id'],'is_pay'=>0))->order('id','desc')->select();
                    }
                    break;
                case 1:
                    $ret = OrderGoods::where(array('user_id'=>$id,'is_pay'=>$status,'is_send'=>0))->order('id','desc')->select();
                    break;
                case 2:
                    $ret = OrderGoods::where(array('user_id'=>$id,'is_pay'=>1,'is_send'=>1))->order('id','desc')->select();
                    break;
                case 3:
                    $ret = OrderGoods::where(array('user_id'=>$id,'is_pay'=>3))->order('id','desc')->select();//退款
                    break;
                case 4:
                    $where = array('user_id'=>$id,'is_comment'=>0,'is_pay'=>1,'is_send'=>2);
                    $ret = OrderGoods::where($where)->order('id','desc')->select();
                    break;
                case 5:
                    $where = array('user_id'=>$id,'is_comment'=>1,'is_pay'=>1,'is_send'=>2);
                    $ret = OrderGoods::where("user_id=$id and is_comment = 1 and is_pay=1 and is_send =2")->whereOr('user_id ='.$id.' and is_pay =5')->order('id','desc')->select();
                    break;

            }
            return json($ret);

        }elseif(!empty($order_id)){
            $ret = ShopOrder::get($order_id);
            $ret['goods'] = OrderGoods::where(array("order_id"=>$order_id))->order('id','desc')->select();
            return json($ret);
        }else{
            $where = array('user_id'=>$id,'order_status'=>$status);
            $ret = ShopOrder::where($where)->order('add_time','desc')->select();
            foreach ($ret as $val){
                $val['goods'] = OrderGoods::where("order_id",$val['id'])->order('id','desc')->select();
            }
            return json($ret);
        }

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
        $order = new ShopOrder();

        if (isset($data['id'])){
            $ret = $order->save($data,['id'=>$data['id']]);
        }else{
            $ret = $order->Posts($data);
        }
//        trace($ret,'return订单');
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
     * order_status 0 代付款 1付款，待取货 2评价  3退款 4完成,5退款完成，6取消
     */
    public function edit($id)
    {
        $user_id = input('get.user_id');
        $shop = input('get.shop');
        $order_id = input('order');

//        $ret = ShopOrder::where('id',$id)->update(['order_status'=>3]);
        //发送退款申请
        $item = OrderGoods::where('id',$id)->find();
        OrderGoods::where('id',$id)->setField(['is_pay'=>3]);

            $data = array();
            $data['user_id'] = $item['user_id']; //缓过来
            $data['user_name'] = $item['user_name']; //缓过来
            $data['owner_id'] = $item['owner_id'];
            $data['goods_id'] = $item['goods_id'];
            $data['goods_name'] = $item['goods_name'];
            $data['create_time'] = time();
            $data['brief'] = $item['user_name'].'申请退款,请去买家订单查看';
            $data['type'] = 3;//上架
            db('ShopMessages')->insert($data);


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
        $item = OrderGoods::where('id',$id)->find();
        OrderGoods::where('id',$id)->setField(['is_send'=>2,'rec_time'=>date('Y-m-d H:i:s',time())]);

        $data = array();
        $data['user_id'] = $item['user_id']; //缓过来
        $data['user_name'] = $item['user_name']; //缓过来
        $data['owner_id'] = $item['owner_id'];
        $data['goods_id'] = $item['goods_id'];
        $data['goods_name'] = $item['goods_name'];
        $data['create_time'] = time();
        $data['brief'] = $item['user_name'].'-买家确认收货';
        $data['type'] = 3;//上架
        $ret = db('ShopMessages')->insert($data);
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
        $ret = ShopOrder::where('id',$id)->delete();
        OrderGoods::where('order_id',$id)->delete();

        return $ret;
    }
}
