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
namespace app\common\model;

use think\Model;
use think\Db;

class Order extends Model {

    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'order_create_time';
    protected $updateTime = false;


	const PAY_TYPE_NULL      = 0; //未设置付款方式
	const PAY_TYPE_FREE      = 1; //免费无需付款
	const PAY_TYPE_CACHE     = 2; //货到付款
	const PAY_TYPE_ALIPAY    = 10; //支付宝
	const PAY_TYPE_WEIXINPAY = 11; //微信支付


	const ORDER_WAIT_USER_PAY     = 1; //待付款
	const ORDER_WAIT_FOR_DELIVERY = 2; //待发货
	const ORDER_WAIT_USER_RECEIPT = 3; //待收货
	const ORDER_DELIVERY_OK       = 4; //已收货
	const ORDER_COMMENT_OK        = 5; //已评价
	const ORDER_NEGOTATION_OK     = 6; //协商完成
	const ORDER_UNDER_NEGOTATION  = 8; //协商中(退货,换货)
	const ORDER_SHOP_CANCELED     = 9; //店家已取消
	const ORDER_CANCELED          = 10; //已取消
	const ORDER_WAIT_SHOP_ACCEPT  = 11; //等待卖家确认


    //字段修改
    public function setMpIdAttr($value)
    {
        return get_mpid();
    }
    public function setBuyerOpenidAttr($value)
    {
        return get_openid();
    }

    public function getOrderStatusAttr($value)
    {
        $status = [0=>'待付款',1=>'已付款',2=>'待退款',3=>'已退款'];
        return $status[$value];
    }

	/*
	 * 获取订单列表
	 */
	public function get_order_list($option)
	{

		if (!empty($option['order_status']))
		{
			$where_arr[] = 'order_status = ' . $option['order_status'];
		}

		if (!empty($option['order_create_time']))
		{ //最近一段时间的订单
			$where_arr[] = 'date_sub(now(), INTERVAL '.$option['order_create_time'].' DAY) <= from_unixtime(order_create_time)' ;
		}
		if (!empty($option['id']))
        {
			$where_arr[] = 'id ='. $option['id'];
		}
		if (!empty($option['mobile']))
		{ //搜索订单号  和 商品名称
			$where_arr[] = 'receiver_mobile like "%' .$option['mobile'] .'%"';
		}
		$where_str = '';
		if (!empty($where_arr))
		{
			$where_str .=  implode(' and ', $where_arr);
		}
		$ret['list']  = $this->where($where_str)->order('order_create_time desc')->paginate($option['r'],true,[$option['page'] ]);
		$ret['count'] = $this->where($where_str)->count();
		return $ret;
	}


	/*
	 * 获取订单
	 */
	public function get_order_by_id($id)
	{
		$ret =$this->where('id =' . $id)->find() ;
		return $ret;
	}


	protected function _after_select(&$ret,$option)
	{
		array_walk($ret,
			function(&$a){
				$this->func_get_oreder($a);
			});
	}

	protected function _after_find(&$ret,$option)
	{

			$this->func_get_oreder($ret);

	}

	private function func_get_oreder(&$a)
	{
		$a['products'] =(!empty($a['products']) ? json_decode($a['products'],true)  : '');
		$a['address'] =(!empty($a['address']) ? json_decode($a['address'],true)  : '');
		$a['info'] =(!empty($a['info']) ? json_decode($a['info'],true)  : '');
		$a['product_cnt'] = count($a['products']);
		$a['product_quantity'] = array_sum(array_column($a['products'],'quantity'));
		$a['product_title'] = (empty($a['products'])?'':$a['products'][0]['title']);
	}



	/*
	 * 编辑订单
	 */
	public function add_or_edit_order($order)
	{
        trace($order,'insert');
//        $order['order_total_price'] = $order['order_total_price'] * 100;
		if (empty($order['id']))
		{
			$ret = $this->allowField(true)->save($order);
			if($ret){
			    $ret = $this->where('id',$this->id)->find();
            }
		}
		else
		{
			$ret = $this->allowField(true)->save($order,['id=' . $order['id']]);
			$ret = $this->order_id;
		}

		return $ret;
	}

	/*
	 * 删除订单
	 */
	public function delete_order($ids)
	{
		if (!is_array($ids))
		{
			$ids = array($ids);
		}

		$ret =  $this->where('id in (' . implode(',', $ids) . ')')->delete();
		return $ret;
	}

	public function get_order_status_list_select()
	{
		return array(
			array('id' => 0, 'value' => '全部'),
			array('id' => self::ORDER_WAIT_USER_PAY, 'value' => '待付款'),
//			array('id' => self::ORDER_WAIT_SHOP_ACCEPT, 'title' => '待接单'),
			array('id' => self::ORDER_WAIT_FOR_DELIVERY, 'value' => '待发货'),
			array('id' => self::ORDER_WAIT_USER_RECEIPT, 'value' => '待收货'),
			array('id' => self::ORDER_UNDER_NEGOTATION, 'value' => '待退款'),
			array('id' => self::ORDER_DELIVERY_OK, 'value' => '已完成'),
			array('id' => self::ORDER_COMMENT_OK, 'value' => '已评价'),
			array('id' => self::ORDER_CANCELED, 'value' => '已取消'),
			array('id' => self::ORDER_SHOP_CANCELED, 'value' => '卖家取消'),
			array('id' => self::ORDER_NEGOTATION_OK, 'value' => '已退款'),
		);
	}

	public function get_order_status_config_select()
	{
		return array(
			self::ORDER_WAIT_USER_PAY=> '待付款',
//			self::ORDER_WAIT_SHOP_ACCEPT=> '待接单',
			self::ORDER_WAIT_FOR_DELIVERY=> '待发货',
			self::ORDER_WAIT_USER_RECEIPT=> '待收货',
			self::ORDER_UNDER_NEGOTATION=> '待退款',
			self::ORDER_DELIVERY_OK => '已完成',
			self::ORDER_COMMENT_OK=> '已评价',
			self::ORDER_CANCELED=> '已取消',
			self::ORDER_SHOP_CANCELED=> '卖家取消',
			self::ORDER_NEGOTATION_OK=> '已退款',
		);
	}
}

