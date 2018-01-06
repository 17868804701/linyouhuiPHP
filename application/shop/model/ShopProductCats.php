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

class ShopProductCats extends Model{
    protected $autoWriteTimestamp = true;
    // 关闭自动写入update_time字段
    protected $updateTime = false;
	/*
	 * 增加修改商品分类
	 */
	public function add_or_edit_product_cats($product_cats){
		if(empty($product_cats['id']))
		{
            $ret = $this->allowField(true)->save($product_cats);
		}
		else
		{
			$ret = $this->allowField(true)->save($product_cats,['id'=>$product_cats['id']]);
		}
		return $ret;
	}
	/*
	 * 删除商品分类
	 */
	public function delete_product_cats($ids)
	{
		if(!is_array($ids))
		{
			$ids = array($ids);
		}
		//$this->startTrans();
		$ret1 = $this->destroy($ids);
		return $ret1;
	}
	/*
	 * 获取分类信息
	 */
	public function get_product_cats($option)
	{
		
		if(isset($option['parent_id']) && $option['parent_id'] >= 0) {
			$where_arr[] = 'parent_id = '.$option['parent_id'];
		}
		if(isset($option['status'])) {
			$where_arr[] = 'status = '.$option['status'];
		}
		$where_str ='';
		if(!empty($where_arr)) {
			$where_str .= implode(' and ', $where_arr);
		}
		if(empty($option)){
            $option['page'] = 1;//当前页
            $option['r']=10;//总页数
        }
        $ret['list'] = $this->where($where_str)->order('sort desc, create_time')->select();//paginate($option['r'], true, ['page'=>$option['page']]);
//		foreach ($ret['list'] as $row){
//		    $row['image'] = get_cover($row['image'],'url');
//        }
//		$ret['page'] = $ret['list']->render();
		$ret['count'] = $this->where($where_str)->count();
		//获取父级分类信息
		if(!empty($option['with_parent_info']) && $ret['list']) {
			foreach($ret['list'] as $k => $c) {
				if($c['parent_id']) {
					$ret['list'][$k]['parent_cat'] = $this->get_product_cat_by_id($c['parent_id']);
				}
			}
		}
		return $ret;
	}

	/*
	 * 获取某个分类信息
	 */
	public function get_product_cat_by_id($id)
	{
		$ret = $this->where('id = '.$id)->find();
		return $ret;
	}

	/*
	 * 生成 可用于 config select 位置的 数组
	 */
	public function get_produnct_cat_config_select($show_titile='顶级分类')
	{
		$option = array();
		$parent = $this->get_product_cats($option);
		$parent = model('Common/Tree')->toFormatTree($parent['list'],$title = 'title',$pk='id',$pid = 'parent_id',$root =
            0);
		$all_cats =array_merge(array(0=>array('id'=>0,'title_show'=>$show_titile)), $parent);
		foreach($all_cats as $cat)
		{
//			$select[$cat['id']] = strtr($cat['title_show'],array('&nbsp;'=>'/&nbsp;'));
			$select[$cat['id']] = html_entity_decode ($cat['title_show']);
		}
		return $select;
	}

	/*
	 * 生成 可用于 list select 位置的 数组 （主要是 列表页下来筛选）
	 */
	public function get_produnct_cat_list_select($show_titile='顶级分类')
	{
		$option = array();
		$parent = $this->get_product_cats($option);
		$parent = model('Common/Tree')->toFormatTree($parent['list'],$title = 'title',$pk='id',$pid = 'parent_id',
            $root = 0);
		$all_cats =array_merge(array(0=>array('id'=>0,'title_show'=>$show_titile)), $parent);
		foreach($all_cats as $cat)
		{
//			$select[] = array('id'=>$cat['id'],'value'=>strtr($cat['title_show'],array('&nbsp;'=>"*")));
			$select[] = array('id'=>$cat['id'],'value'=>html_entity_decode ($cat['title_show']));


		}
		return $select;
	}

	/*
	 * 获取在 这个父分类下所有的分类id
	 *
	 */
	public function get_all_cat_id_by_pid($pid)
    {
        $ret = $this->where('parent_id', $pid)->select();
        return $ret;
    }
}

