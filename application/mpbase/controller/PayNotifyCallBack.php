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

use app\common\model\OrderGoods;
use com\TPWechat;
use com\wxpay\WxPayApi;
use com\wxpay\database\WxPayNotify;
use com\wxpay\database\WxPayOrderQuery;

class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->setTransactionId($transaction_id);
		$result = WxPayApi::orderQuery($input);

		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理方法，成功的时候返回true，失败返回false，处理商城订单
	public function NotifyProcess($data, &$msg)
	{
//	    trace($data,'data');
//	    trace($msg,'msg');

		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}

        //以上的代码都是相同的，以下代码写定制业务逻辑，这里实现一个通用订单处理逻辑
        $transaction = model('transaction'); // 保存微信支付订单流水
        trace($data,'NotifyProcess');
        trace($msg,'NotifyProcessmsg');
        $transaction->data($data);
        $transaction->save();

        $map["out_trade_no"] = $data["out_trade_no"];
        $omap["order_sn"] = $data["out_trade_no"];
        trace($omap,'omap111');
//        $data["paySta"] = 1;
        $trans_id = $data["transaction_id"];
        $order = model('shop_order');
        $order-> where($omap)->setField(["transaction_id"=>$data["transaction_id"],'order_status'=>1,'pay_time'=>time()]); //支付流水号写入订单
        $order_id = $order->where($omap)->value('id');
        trace($order_id,'返回的订单编号');
        $ordergoods = db('OrderGoods')->where('order_id',$order_id)->select();
        foreach ($ordergoods as $item){
            $goods = OrderGoods::get($item['id']);
            trace($goods,'bain里玄幻');
            $goods->is_pay= 1;
            $goods->order_sn= $trans_id;
            $goods->pay_time = date('Y-m-d H:i:s',time());
            $goods->save();

            //付款消息
            $data = array();
            $data['user_id'] = $goods->user_id; //缓过来
            $data['owner_id'] = $goods->owner_id;
            $data['goods_id'] = $goods->id;
            $data['goods_name'] = $goods->goods_name;
            $data['user_name'] = $goods->user_name;
            $data['create_time'] = time();
            $data['brief'] = '商品:'.$data['goods_name'].'-买家:'.$goods->user_name.'已付款，请去买家订单查看';
            $data['type'] = 3;//上架
            db('ShopMessages')->insert($data);
//            $goods = '';

            //减去库存

            $spec = db('SpecGoodsPrice')->where('goods_id',$goods->goods_id)->find();
            $spec_id = $spec['spec_id'];
            $spevArr = array('status'=>false,'data'=>'数量不足，提交失败');
            if ($spec){

                switch ($goods->spec_key_name){
                    case $spec['name1']:
                    $spec['store1'] -= $item['goods_num'];
                        if ($spec['store1'] <0){
                            return json($spevArr);
                        }
                        break;
                    case $spec['name2']:
                    $spec['store2'] -= $item['goods_num'];
                        if ($spec['store2'] <0){
                            return json($spevArr);
                        }
                        break;
                    case $spec['name3']:
                    $spec['store3'] -= $item['goods_num'];
                        if ($spec['store3'] <0){
                            return json($spevArr);
                        }
                        break;
                }
            db('ShopGoods')->where('id',$item['goods_id'])->setInc('sales_sum',$item['goods_num']);
            db('SpecGoodsPrice')->where('spec_id',$spec_id)->update($spec);
            }


        }

		return true;
	}
}

