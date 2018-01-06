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
namespace app\shop\model;
 use think\Model;

 //define('NOW_TIME',input('server.REQUEST_TIME'));
class ShopProduct extends Model {

    protected $autoWriteTimestamp = true;
    // 关闭自动写入update_time字段
    protected $updateTime = false;
	/*
	 * 编辑商品
	 */
	public function add_or_edit_product($product)
	{
        if(!empty($product['price_int'])){
            if (is_array($product['price_int'])){
                        $product['price_int'] = implode(',',$product['price_int']);
                    }
        }
		if(empty($product['id']))
		{

			$ret = $this->allowField(true)->save($product);
		}
		else
		{
//			$this->create();
			$ret = $this->allowField(true)->save($product,['id'=>$product['id']]);
		}
		return $ret;
	}

	/*
	 * 删除商品
	 */
	public function delete_product($ids)
	{
		if(!is_array($ids))
		{
			$ids = array($ids);
		}
		$ret = $this->where('id in ('.implode(',',$ids).')')->delete();
		return true;

	}

	/*
	 * 获取商品信息
	 */
    public function get_product_by_id($id)
    {
        $ret = $this->where('id',$id)->find();
        $ret['image_id'] = $ret['image'];
        $ret['image'] = get_cover($ret['image'],'url');
        if (!empty($ret['price_int'])){
            $ret['price_int'] = explode(',',$ret['price_int']);
        }

        return $ret;
    }

	public function get_product_list($option)
	{
		if(isset($option['cat_id']) && $option['cat_id'] >= 0) {
			$where_arr[] = 'cat_id = '.$option['cat_id'];
		}
		if(isset($option['status'])) {
			$where_arr[] = 'status = '.$option['status'];
		}
		$where_str ='';
		if(!empty($where_arr)) {
			$where_str .= implode(' and ', $where_arr);
		}
		$ret['list'] = $this->where($where_str)->order('sort desc, create_time')->paginate($option['r'], true, ['page'=>$option['page']]);
        //换图片
		foreach ($ret['list'] as $row){
            $row['image'] = get_cover($row['image'],'url');
        }
		$ret['count'] = $this->where($where_str)->count();

		return $ret;
	}


	/*
	 * 通过sku_id 获取商品
	 */
	public function get_product_by_sku_id($sku_id)
	{
		$sku_id = explode(';', $sku_id, 2);
		$product_id = $sku_id[0];

		$where_arr[] = 'id = '.$product_id.'';
		$where_str ='';
		if(!empty($where_arr)) {
			$where_str .= implode(' and ', $where_arr);
		}
		$ret = $this->where($where_str)->find();
		$ret['quantity_total'] = $ret['quantity'];
		if(!empty($sku_id[1]) && !empty($ret['sku_table']['info'][$sku_id[1]])) {
			$ret = array_merge($ret, $ret['sku_table']['info'][$sku_id[1]]);
		}
		unset($ret['sku_table']);
		$ret['sku_id'] = $sku_id;
		return $ret;
	}

	protected function _after_find(&$ret,$option)
	{
		if(!empty($ret['sku_table'])) $ret['sku_table'] = json_decode($ret['sku_table'],true);
	}

	protected function _after_select(&$ret,$option)
	{
		if(!empty($ret['sku_table'])) $ret['sku_table'] = json_decode($ret['sku_table'],true);
	}


	/*
	 * 取某个分类、某几个分类下所有分类的商品id
	 */
	public function get_all_product_id_by_cat_id($cat_id)
	{
		is_array($cat_id) || $cat_id = array($cat_id);
		$ret = $this->where('cat_id in ('.implode(',',$cat_id).')')->field('id')->select();
		is_array($ret) && $ret = array_column($ret,'id');
		return $ret;
	}

	/*
	 * 编辑添加关于我们的信息
	 */
	public function edit_about($data){
        if ($data['id'] == 1){
            $ret = db('shop_about')->where('id',$data['id'])->update($data);
        }else{
            $ret = db('shop_about')->insert($data);
        }
        return $ret;
    }

    public function get_about_by_id($id){
	    $ret = db('shop_about')->find($id);
	    return $ret;
    }

    /*
     *编辑管理价格
     **/
    public function add_or_edit_price($data){
        if (empty($data['id'])){
            $ret = db('shop_price')->insert($data);
        }else{
            $ret = db('shop_price')->where('id',$data['id'])->update($data);
        }
        return $ret;
    }

    public function get_price_by_select(){
        $ret = db('shop_price')->where('status',0)->select();
        //获取栏目名称
        foreach ($ret as &$row){
            $row['cat_title'] = $this->get_cat_title($row['cat_id']);
        }
        return $ret;
    }

    public function get_cat_title($id){
        $title = db('shop_product_cats')->where('id',$id)->find();
        //dump($ret);
        if (empty($title)){
            $title = '全部商品';
            return $title;
        }else{
            return $title['title'];
        }

    }

    public function get_price_by_id($id){
        if(is_string($id)){
            $arr = explode(',',$id);
            $ret = array();
            for ($i=0;$i<count($arr);$i++){
                $ret[] = db('shop_price')->where('id',$arr[$i])->find();
            }
        }else{
            $ret = db('shop_price')->find($id);
        }

        return $ret;
    }

    public function delete_price($ids)
    {
        if(is_array($ids))
        {
            $ids = array($ids);
        }
        $ret = db('shop_price')->delete($ids);
        return true;

    }
}

