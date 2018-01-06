<?php
// +----------------------------------------------------------------------
// | UCToo [ Universal Convergence Technology ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://uctoo.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: UCT <contact@uctoo.com>
// +----------------------------------------------------------------------

namespace app\shop\controller;

use app\admin\controller\Admin;
use app\common\model\OrderGoods;
use think\Model;
use \think\Loader;
use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminListBuilder;
use app\admin\builder\AdminTreeListBuilder;
use think\Db;
//use Think\Db\Driver\Pdo;


class Shop extends Admin
{
    protected $product_cats_model;
    protected $product_model;
    protected $order_model;
    protected $delivery_model;
    protected $message_model;
    protected $coupon_model;
    protected $user_coupon_model;
    protected $address_model;
    protected $product_comment_model;

	protected $order_logic;
	protected $coupon_logic;

    function _initialize()
    {
        $this->product_cats_model = model('ShopProductCats');
	    $this->product_model = model('ShopProduct');
	    $this->order_model = model('ShopOrder');
	    $this->order_logic = model('ShopOrder','Logic');
	    $this->product_comment_model = model("ShopProductComment");
        $this->delivery_model = model('ShopDelivery');
        parent::_initialize();
    }


	public function index()
	{

        $list = db('shop_product')->select();
        $totalCount = db('shop_product')->count();
        $r = 10;
        //显示页面
        $builder = new AdminListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';

        $builder
            ->title('商品列表')
            ->buttonNew(url('shop/product/action/add'))
            ->setStatusUrl(url('setStatus'))->button(lang('_DELETE_'),array('class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确实要删除公众号吗？（删除后对应的公众号配置将会清空，不可恢复，请谨慎删除！）', 'url' => url('del'), 'target-form' => 'ids'))
            ->keyId()->keyText('product_name', '商品名称')->keyText('image', '图片')->keyText('cat_title', '所属栏目')->keyText
            ('create_time','时间')->keyText('sort','排序')
            ->keyStatus()->keyDoActionEdit('edit?id=###')->keyDoAction('del?ids=###', lang('_DELETE_'))
            ->keyDoAction('mpbase/Mpbase/help?id=###', '帮助')
            ->data($list)
            ->pagination($totalCount, $r);
        return $builder ->fetch();
//		return $this->fetch('product');
	}

	/*
	 * 群组功能
	 */
	public function shop($action='')
	{
        $shop = model('Shop');
        switch($action)
        {
            case 'add':
                $build = new AdminConfigBuilder();
                if (request()->isPost()){
                    $data = input('');
                    unset($data['action']);
                    foreach ($data as $key => $item){
                        if ($item == '')    unset($data[$key]);
                    }

                    trace($data,'data');
                    if(empty($data['shop_name'])){
                        return ['status'=>true,'info'=>'请输入标题'];
                    }

                    $res = $shop->create_shop($data);

                    if($res){
                        return ['status'=>true,'info'=>'新增成功','url'=>url('shop/shop')];
                    }else{
                        return ['status'=>true,'info'=>'新增失败'];
                    }
                }else
                {
                    $id = input('id');
                    if(!empty($id))
                    {
                        $product = $shop->find($id);
                    }else{
                        $product = array();
                    }

                    //$tag = $this->product_cats_model->column('title');
                    $shop_admin = db('ucuser')->order('mid','desc')->column("mid,nickname");

                    $build->title('新增/修改商品')
                        ->keyId('id','id')
                        ->keyText('shop_name', '店铺/群名称')
                        ->keyMultiImage('shop_image','图片(最多五张)')
                        ->keyTextArea('shop_content','群介绍')
                        ->keySelect('shop_owner_id','管理员','',$shop_admin)
                        ->keyText('address1','自提地址1')
                        ->keyText('address2','自提地址2')
                        ->keyText('address3','自提地址3')
                        ->keyText('address4','自提地址4')
                        ->keyRadio('status','状态','',array('0'=>'正常','1'=>'禁用'))
//                        ->keyText('sort','排序','默认1')
                        ->keyCreateTime('create_time')
                        ->data($product)
                        ->buttonSubmit(url('@shop/shop/shop/action/add'))
                        ->buttonBack();
                    return $build->fetch();
                }
                break;
            case 'delete':
                $ids = input('ids/a');
                if(!is_array($ids))
                {
                    $ids = array($ids);
                }
                $ret = $shop->destroy($ids);
                if ($ret)
                {

                    return ['status'=>true,'info'=>'操作成功。', url('shop/product')];
                }
                else
                {
                    return ['status'=>true,'info'=>'操作失败。'];
                }
                break;

            default:
                $option['page'] = input('page',1);//页数
                $option['r'] = input('r',10);//每页显示数
                $option['cat_id'] = input('cat_id');
                $option['status'] = input('status');
                if(empty($option['cat_id'])) unset($option['cat_id']);
                $product = db('shop')->select();
//				dump($product);
                $totalCount = $shop->count();
                //输出页面
                $builder = new AdminListBuilder();
                $builder
                    ->title('新增店铺')
                    ->buttonNew('shop/action/add','新增店铺')
                    ->ajaxButton(url('shop/shop',array('action'=>'delete')),array(),'删除')
//                    ->keyId('id','商品ID')
                    ->keyText('id','ID')
                    ->keyText('shop_name','商店/群标题')
                    ->keyImage('image','二维码')
                    ->keyOwner('shop_owner_id', '群主姓名-微信-电话')
                    ->keyText('shop_member', '会员数量')
                    ->keyTime('create_time','时间')
                    ->keyMap('status','状态',array('0'=>'正常','1'=>'禁用'))
                    ->keyDoAction('@shop/shop/shop/action/add/id/###','编辑')
                    ->data($product)
                    ->pagination($totalCount, $option['r']);
                return $builder->fetch();
                break;
        }

	}

	/*
	 * 审核加入
	 */
	public function apply($action = ''){
	    switch ($action){
            case 'add':
                break;
            case 'delete':
                break;
            default:
                $builder = new AdminListBuilder();
                $builder->title('审核信息')
                    ->ajaxButton(url(),array(),'审核通过')
                    ->select('订单状态：', 'status', 'select', '', '', '', array('1'))
                    ->select('显示模式:', 'show_type', 'select', '', '', '', array('2'))
                    ->keyText('shop_name','标题')
                    ->keyText('shop_content')
                    ->keyText('status','状态',array('0'=>'在售','1'=>'下架'))
                    ->keyTime('create_time','创建时间');
                    return $this->fetch();
        }
    }


    /*
        * 幻灯片
        */
    public function slides($action='')
    {
        $shop_slides_model = db('shop_slides');
        switch ($action)
        {
            case 'add':
                if(request()->isPost())
                {
                    $slides = input('post.');
                    $slides['sort'] = (empty($slides['sort'])?0:$slides['sort']);
                    trace($slides,'幻灯片');

                    if(empty($slides['image']))
                    {
                        return ['status'=>true,'info'=>'请设置一张图片'];
                    }
                    if(!empty($slides['id']))
                    {
                        unset($slides['create_time']);
                        $ret = $shop_slides_model->where('id',$slides['id'])->update($slides);
                    }
                    else
                    {
                        $ret = $shop_slides_model->insert($slides);
                    }
                    trace($ret,'结果');
                    if($ret)
                    {
                        return ['info'=>'添加成功','url'=>url('shop/slides')];
                    }
                    else
                    {
                        return ['status'=>true,'info'=>'添加失败','msg'=>'失败'];
                    }
                }
                else
                {
                    $id = input('id');
                    if(!empty($id)){
                        $slides = $shop_slides_model->where('id ='.$id)->find();
                    }else{
                        $slides = array();
                    }
                    $builder = new AdminConfigBuilder();
                    $builder->title('添加/编辑幻灯片')
                        ->keyId('id','编号')
                        ->keyText('title','标题')
                        ->keySingleImage('image','图片')
                        ->keyText('link','链接地址')
                        ->keyText('sort','排序')
                        ->keyRadio('status','状态','',array('0'=>'正常','1'=>'禁用'))
                        ->keyCreateTime('create_time')
                        ->data($slides)
                        ->buttonSubmit(url('@shop/shop/slides/action/add'))
                        ->buttonBack();
                    return $builder->fetch();
                }
                break;
            case 'delete':
                $ids = input('ids/a');
                if (!is_array($ids))
                    $ids = array($ids);
                $ret = $shop_slides_model->delete($ids);
                if($ret)
                {
                    return ['status'=>true,'info'=>'删除成功'];
                }
                else
                {
                    return ['status'=>true,'info'=>'删除失败'];
                }
                break;
            default:
                $page = input('page',1);//页数
                $r = input('r',10);//每页显示数
                $slides = $shop_slides_model->select();
                $count = $shop_slides_model->count();
                $builder = new AdminListBuilder();
                $builder
                    ->title('幻灯片')
                    ->buttonNew('slides/action/add','新增幻灯片')
                    ->ajaxButton(url('shop/slides',array('action'=>'delete')),array(),'删除')
//                    ->keyId('id','商品ID')
                    ->keyID('id','ID')
                    ->keyText('title','标题')
                    ->keyImage('image','图片')
                    ->keyText('sort', '排序')
                    ->keyTime('create_time','时间')
                    ->keyMap('status','状态',array('0'=>'正常','1'=>'禁用'))
                    ->keyDoAction('@shop/shop/slides/action/add/id/###','编辑')
                    ->data($slides)
                    ->pagination($count, $r);
                return $builder->fetch();

                break;
        }
    }

	/*
	 * 商品分类
	 */
	public function product_cats($action='',$page=1,$r=10)
	{

		switch($action)
		{
			case 'add':
			    //输出级联列表
                //$cate = $this->product_cats_model->column('title');
			    //$cates = array_merge(array('title'=>'顶级目录'),$cate);
//                $this->assign("tag",$cate);
				if(request()->isPost())
				{
					$product_cats = input('');//$this->product_cats_model->create();
					if (!$product_cats){
						$this->error($this->product_cats_model->getError());
					}
					//不能把自己或者自己的子类当作上级目录
					if(!empty($product_cats['parent_id'] )	&& (($product_cats['parent_id'] ==$product_cats['id']) ))
					{
						return ['status'=>true,'info'=>'不要选择自己分类或自己的子分类'];
					}

                    //添加或者编辑数据
					$ret = $this->product_cats_model->add_or_edit_product_cats($product_cats);
					if ($ret)
					{

                        return ['data'=>'','status'=>true,'info'=>'操作成功。', 'url'=>url('shop/product_cats',array
                        ('parent_id'=>input('parent_id',0)))];
					}
					else
					{
                        return ['data'=>'','status'=>true,'info'=>'操作失败'];
					}
				}else
				{
				    $parent_id = input('parent_id');
                    //获取当前下属类别
                    if($parent_id != 0)
                    {
                        $product_cats = $this->product_cats_model->get_all_cat_id_by_pid($parent_id);
                    }
					$id = input('id');
					if(!empty($id))
					{
						$product_cats = $this->product_cats_model->get_product_cat_by_id($id);
					}
					else
					{
						$product_cats = array();
					}
//                    $this->assign('list',$product_cats);
                    $builder = new AdminConfigBuilder();
                    $builder->title('新增/修改商品分类')
                        ->keyId()
                        ->keyText('title', '分类名称')
                        ->keySingleImage('image','图片')
                        ->keyText('sort', '排序')
                        ->keyRadio('stauts','状态','',array('0'=>'正常','1'=>'隐藏'))
                        ->keyCreateTime()
                        ->data($product_cats)
                        ->buttonSubmit(url('shop/product_cats',array('action'=>'add')))
                        ->buttonBack();
					return $builder->fetch();
				}
				break;
			case 'delete':
				$ids = input('ids/a');
				$ret = $this->product_cats_model->delete_product_cats($ids);
				if ($ret)
				{

					return ['status'=>true,'info'=>'删除成功。', url('shop/product_cats')];
				}
				else
				{
					$this->error('删除失败。');
				}
				break;
			default:

				$select = db('shop_product_cats')->select();
                $totalCount = $this->product_cats_model->count();
                $builder = new AdminListBuilder();

                $attr['class'] = 'btn ajax-post';
                $attr['target-form'] = 'ids';
                $builder
                    ->title((empty($parent_cat)?'顶级的':$parent_cat['title'].' 的子').'商品分类')
                    ->setSelectPostUrl(url('shop/product_cats'))
                    ->buttonNew(url('shop/product_cats',array('action'=>'add')),'新增分类')
                    ->ajaxButton(url('shop/product_cats',array('action'=>'delete')),array(),'删除')
                    ->keyText('title','标题')
                    ->keyImage('image','图片')
                    ->keyText('sort','排序')
                    ->keyTime('create_time','创建时间')
                    ->keyStatus('status','状态')
                    ->keyDoAction('@shop/shop/product_cats/action/add/id/###','编辑')
                    ->data($select)
                   ->pagination($totalCount, $r);
				return $builder->fetch();
		}
	}

	/*
	 * 商品相关
	 */
	public function product($action = '')
	{
        $goods = model('shop_goods');
		switch($action)
		{
			case 'add':
			    $build = new AdminConfigBuilder();
                if (request()->isPost()){
                    $data = input('');
                    trace($data,'data');
                    if(empty($data['goods_name'])){
                        return ['status'=>true,'info'=>'请输入标题'];
                    }
                    $res = $goods->EditGoods($data);



                    if($res){
                        return ['data'=>'','status'=>true,'info'=>'新增成功','url'=>url('shop/product')];
                    }else{
                        return ['status'=>true,'info'=>'新增失败'];
                    }
                }else
                {
                    $id = input('id');
                    $db_prefix=config('table_prefix');//表前缀
                    if(!empty($id))
                    {
                        $product = db('shop_goods')->find($id);
                        $spec = db('spec_goods_price')->where('goods_id',$id)->field('name1,name2,name3,price1,price2,price3,store1,store2,store3')->find();
                        if($spec){

                            $product = array_merge($product,$spec);
                        }else{
                            $spec = array(
                                'name1'=>'','price1'=>0,'store1'=>0,
                                'name2'=>'','price2'=>0,'store2'=>0,
                                'name3'=>'','price3'=>0,'store3'=>0,

                            );
                            $product = array_merge($product,$spec);
                        }
//                        dump($product);
                    }else{


                    }

                    $select = db('shop_product_cats')->column('id,title');$this->product_cats_model->get_produnct_cat_config_select('shop_product_cats','title','parent_id');

//                   $shop = $this->product_cats_model->get_produnct_cat_config_select('shop','shop_name','','全部');
                    $shop = db('shop')->column("id,shop_name");
                    $build->title('新增/修改商品')
                        ->keyId('id','id')
                        ->keyText('goods_name', '商品名称')
                        ->keySingleImage('image','图片','单图')
                        ->keyMultiImage('goods_img','详情图片集','最多5张');
                    if($spec) {
                        $build->keyMultiInput('name1|price1|store1', '商品规格1', '规格描述|定价|储存', array(
                            array('type' => 'text', 'style' => 'width:100px;margin-right:5px', 'placeholder' => ''),
                            array('type' => 'text', 'style' => 'width:100px;margin-right:5px', 'placeholder' => ''),
                            array('type' => 'text', 'style' => 'width:100px;margin-right:5px', 'placeholder' => ''),
                        ))
                            ->keyMultiInput("name2|price2|store2", '商品规格2', '规格描述|定价|储存', array(
                                array('type' => 'text', 'style' => 'width:100px;margin-right:5px', 'placeholder' => ''),
                                array('type' => 'text', 'style' => 'width:100px;margin-right:5px', 'placeholder' => ''),
                                array('type' => 'text', 'style' => 'width:100px;margin-right:5px', 'placeholder' => '')
                            ))
                            ->keyMultiInput("name3|price3|store3", '商品规格3', '规格描述|定价|储存', array(
                                array('type' => 'text', 'style' => 'width:100px;margin-right:5px', 'placeholder' => ''),
                                array('type' => 'text', 'style' => 'width:100px;margin-right:5px', 'placeholder' => ''),
                                array('type' => 'text', 'style' => 'width:100px;margin-right:5px', 'placeholder' => ''),
                            ));
                    }
                        $build->keyTextArea('goods_content','商品介绍')
                        ->keyCatSelect('cat_name','商品分类','',$select)
                        ->keySelect('shop_id','所属店铺','',$shop)
                        ->keyText('goods_price', '商品价格')
//                        ->keyText('store_count', '库存数量')
                        ->keyText('address','自提地址','多个逗号分开')
                        ->keyText('sort', '排序')
                        ->keyRadio('is_on_sale','状态','',array(0=>'在售',1=>'下架',3=>'已删除'))
//                        ->keyRadio('is_hot','最热','',array(0=>'是',1=>'否'))
                        ->keyCreateTime('on_time')
                        ->data($product)
                        ->buttonSubmit(url('shop/product',array('action'=>'add')))
                        ->buttonBack();
                    return $build->fetch();
                }
                break;
			case 'delete':
				$ids = input('ids/a');
				trace($ids,'idd');
				if (!is_array($ids)){
				    $ids = array($ids);
                }
				$ret = $goods->destroy($ids);
				$where = implode(',',$ids);
                db('SpecGoodsPrice')->where("goods_id in ({$where})")->delete();
				if ($ret)
				{

					return ['status'=>true,'info'=>'操作成功。', url('shop/product')];
				}
				else
				{
					return ['status'=>true,'info'=>'操作失败。'];
				}
				break;

			default:
				$option['page'] = input('page',1);//页数
				$option['r'] = input('r',10);//每页显示数
				$option['cat_id'] = input('cat_id');
                $option['status'] = input('status');

                $goods_name = input('key');
                $goods && $order['goods_name'] = array('like','%'.$goods_name.'%');

                //价格排序
                $order['price'] = input('price',0);
                $cat_name = input('catname');//分类
                $cat_name && $order['cat_name']  = $cat_name;
                $oshop_name = input('shopname');//店铺
                $oshop_name && $order['shop_name'] = $oshop_name;

				if(empty($option['cat_id'])) unset($option['cat_id']);

//                trace($order,'shop_goods 1');

				//筛选
                if($order['price'] == 0){
                    unset($order['price']);
                    $product = db('shop_goods')->where($order)->limit(($option['page']-1)*$option['r'],$option['r'])->order('id','desc')->select();
                }elseif($order['price'] == 1){
                    unset($order['price']);
                    $product = db('shop_goods')->where($order)->limit(($option['page']-1)*$option['r'],$option['r'])->order('goods_price','asc')->select();
                }else{
                    unset($order['price']);
                    $product = db('shop_goods')->where($order)->limit(($option['page']-1)*$option['r'],$option['r'])->order('goods_price','desc')->select();
                }

                $cat_name = db('ShopProductCats')->column('title');
                $cats = array();
                $id = 0;
                foreach ($cat_name as $cat){
                    $cats[$id]['id'] = $cat;
                    $cats[$id]['value'] = $cat;
                    ++$id ;
                }
                $shopArr = array();
                $id = 0;
                $shop = db('shop')->column('shop_name,id','id');
                foreach ($shop as $s){
                    $shopArr[$id]['id'] = $s;
                    $shopArr[$id]['value'] = $s;
                    $id++;
                }
				$totalCount = $goods->count();
                //输出页面
                $builder = new AdminListBuilder();
                $builder
                    ->title('新增商品')
//                    ->buttonNew('product/action/add','新增商品')
                    ->ajaxButton(url('shop/product',array('action'=>'delete')),array(),'删除')

                    ->setSearchPostUrl(url('shop/product'))
//                    ->search('', 'id', 'text', '订单id', '', '', '')
                    ->search('', 'key', 'text', '商品名', '', '', '')
                    ->select('筛选：', 'price', 'select', '', '', '', array(array('id'=>0,'value'=>''),array('id'=>1,'value'=>'从低到高'),array('id'=>2,'value'=>'从高到低')))
                    ->select('分类：', 'catname', 'select', '', '', '', $cats)
                    ->select('店铺：', 'shopname', 'select', '', '', '', $shopArr)
//                    ->keyId('id','商品ID')
                    ->keyText('id','商品ID')
                    ->keyText('goods_name','商品标题')
                    ->keyImage('image','图片')
                    ->keyText('cat_name','所属栏目')
                    ->keyShop('shop_id','所属店铺')
                    ->keyTime('on_time','时间')
                    ->keyText('goods_price', '商品价格')
//                    ->keyText('store_count', '库存数量')
                    ->keyText('sort','排序')
                    ->keyMap('is_on_sale','状态',array(0=>'在售',1=>'下架',3=>'已删除'))
                    ->keyDoAction('@shop/shop/product/action/add/id/###','编辑')
                    ->data($product)
                    ->pagination($totalCount,10);
                return $builder->fetch();
				break;
		}
	}

    /*
	 *商品评论
	 */
    public function product_comment($action ='')
    {
        switch($action)
        {
            case 'edit_status':
                if(request()->isPost())
                {
                    $ids  =  input('ids');
                    $status  =  input('get.status','','/[012]/');
                    if(empty($ids) || empty($status))
                    {
                        $this->error('参数错误');
                    }
                    $ret = $this->product_comment_model->edit_status_product_comment($ids,$status);
                    if($ret)
                    {
                        $this->success('操作成功');
                    }
                    else
                    {
                        $this->error('操作失败');
                    }
                }
                break;
            case 'show_pic':
                $id = input('id','','intval');
                $ret = $this->product_comment_model->find($id);
                $this->assign('product_comment',$ret);
//				var_dump(__file__.' line:'.__line__,$ret);exit;
                $this->display('Shop@Shop/show_pic');
                break;
            case 'delete':
                $ids = input('ids/a');
                $ret = db('ShopComment')->delete($ids);
                if($ret){
                    return ['status'=>true,'info'=>'操作成功。', url('')];
                }else{
                    return ['status'=>true,'info'=>'操作失败。', url('')];
                }
                break;
            default:
                $option['page'] = input('page','1','intval');
                $option['r'] = input('r','10','intval');
//                $product_comment  = $this->product_comment_model->get_product_comment_list($option);
//                dump($product_comment['list']);
                $comment = db('ShopComment')->limit(($option['page']-1)*$option['r'],$option['r'])->order('id','desc')->select();
                $count = count($comment);
                $builder = new AdminListBuilder();
                return $builder
                    ->title("商品评论管理")
                    ->ajaxButton(url('shop/product_comment',array('action'=>'delete')),array(),'删除评论')
//                    ->ajaxButton(url('shop/product_comment',array('action'=>'edit_status','status'=>2)),array(),'审核不通过')
                    ->keyId()
//                    ->keyJoin('product_id','商品','id','title','shop_product','/shop/shop/product')
//                    ->keyJoin('order_id','订单','id','id','shop_order','/admin/shop/order')
//                    ->keyJoin('user_id','用户','uid','nickname','member','/admin/user/index')
                    ->keyText('goods_name','商品名字')
                    ->keyText('brief','评论内容')
                    ->keyText('name','用户')
                    ->keyText('create_time','评论时间')
//                    ->keyMap('status','状态',array('0'=>'未审核','1'=>'已通过','2'=>'未通过'))
//					->keyDoActionModalPopup('admin/shop/product_comment/action/show_pic/id/###','查看评论图片','操作')
                    ->data($comment)
                    ->pagination($count, $option['r'])
                    ->fetch();
                break;
        }

    }


    /**
     * @param string $action
     * @return mixed|\think\response\View
     * 消息管li
     */

    public function message(){
        if(request()->isPost()){
            $ids = input('ids/a');
            $ret = db('ShopMessages')->delete($ids);
            if($ret){
                return ['status'=>true,'info'=>'操作成功。', url('')];
            }else{
                return ['status'=>true,'info'=>'操作失败。', url('')];
            }

        }else{
            $page = input('page',1);
            $limit = input('r',10);
            $list = db('ShopMessages')->limit(($page-1)*$limit,$limit)->order('id','desc')->select();
            $count = db('ShopMessages')->count();
            $builder = new AdminListBuilder();
            return $builder
                ->title("消息管理")
                ->ajaxButton(url(''),array(),'删除')
                ->keyId()
                ->keyText('user_name','用户名字')
                ->keyText('brief','内容')
                ->keyText('goods_name','商品名字')
                ->keyTime('create_time','评论时间')
                ->data($list)
                ->pagination($count, $limit)
                ->fetch();
        }
    }


    /**
     * @param string $action
     * @return mixed|\think\response\View
     * 运费模板
     */

    public function delivery(){
        if(request()->isPost()){
            $ids = input('ids/a');
            $ret = db('Delivery')->delete($ids);
            if($ret){
                return ['status'=>true,'info'=>'操作成功。', url('')];
            }else{
                return ['status'=>true,'info'=>'操作失败。', url('')];
            }

        }else{
            $page = input('page',1);
            $limit = input('r',10);
            $list = db('Delivery')->limit(($page-1)*$limit,$limit)->order('id','desc')->select();
            $count = db('Delivery')->count();
            $builder = new AdminListBuilder();
            return $builder
                ->title("模板管理")
                ->ajaxButton(url(''),array(),'删除')
                ->keyId()
                ->keyOwner('user_id','用户')
                ->keyText('type','类型')
                ->keyText('title','标题')
                ->keyText('price','价格')
                ->data($list)
                ->pagination($count, $limit)
                ->fetch();
        }
    }
	//订单
    /*
	 *  订单相关
	 */
    public function order($action= '')
    {
        switch($action)
        {
            case 'delete':
                $ids = input('ids/a');
                $ret = db('OrderGoods')->delete($ids);
                if($ret)
                {
                    return ['info'=>'删除成功','url'=>url('order')];
                }
                else
                {
                    return ['info'=>'删除失败','url'=>url('order')];
                }
                break;
            case 'order_delivery':
                if(request()->isPost())
                {
                    $id = input('id');
                    empty($id) && $this->error('信息错误',1);
                    $courier_no = input('courier_no');
                    $courier_name = input('courier_name');
                    $courier_phone = input('courier_phone','','intval');
                    $delivery_info = array(
                        'courier_no'=>$courier_no,
                        'courier_name'=>$courier_name,
                        'courier_phone'=>$courier_phone,
                    );
                    $order['delivery_info'] = json_encode($delivery_info);
                    $order['id'] = $id;
                    $ret = $this->order_model->add_or_edit_order($order);
                    if($ret)
                    {
                        $this->success('操作成功');
                    }
                    else{
                        $this->error('操作失败','',3);
                    }
                }
                else{
                    $id = input('id');
                    $order = $this->order_model->get_order_by_id($id);
//                    $delivery_info = json_decode($order['delivery_info'],true);
                    //				var_dump(__file__.' line:'.__line__,$order);exit;
                    $delivery_info['id'] = $order['id'];
                    $order['shipping_time'] = (empty($order['shipping_time'])?'未发货':date('Y-m-d H:i:s',$order['shipping_time']));
                    $order['confirm_time'] = (empty($order['confirm_time'])?'未收货':date('Y-m-d H:i:s',$order['confirm_time']));

                    $delivery_info['send_time'] = $order['shipping_time'];
                    $delivery_info['recv_time'] = $order['confirm_time'];
                    $builder       = new AdminConfigBuilder();
                    return $builder
                        ->title('发货信息')
                        ->suggest('发货信息')
                        ->keyReadOnly('id','订单id')
                        ->keyText('shipping_code','快递单号')
                        ->keyText('shipping_name','快递员姓名')
                        ->keyText('shipping_price','邮费')
                        ->keyText('shipping_time','发货时间')
                        ->keyText('confirm_time','收货时间')
                        ->buttonSubmit(url('Shop/order',array('action'=>'order_delivery')),'修改')
                        ->buttonBack()
                        ->data($delivery_info)
                        ->fetch();
                }
                break;
            case 'order_address':
                $id = input('id');
                $order = $this->order_model->get_order_by_id($id);
                $address = is_array($order['address'])?$order['address']:json_decode($order['address'],true);

                $builder       = new AdminConfigBuilder();
                $builder
                    ->title('地址等信息')
                    ->keyReadOnly('id','订单id')
//                    ->keyJoin('user_id','用户','uid','nickname','member','/admin/user/index')
                    ->keyText('pay_name','姓名')
                    ->keyText('mobile','手机')
                    ->keyMultiInput('province|city|twon','地址','省|市|区',array(
                        array('type'=>'text','style'=>'width:95px;margin-right:5px','placeholder'=>''),
                        array('type'=>'text','style'=>'width:95px;margin-right:5px','placeholder'=>''),
                        array('type'=>'text','style'=>'width:95px;margin-right:5px','placeholder'=>''),
                    ))
                    ->keyText('address','详细地址');
                $address = is_array($address)?$address:array();
                $builder->buttonSubmit('')
                    ->buttonBack()

                    ->data($order);
                    return $builder->fetch();
                break;
            case 'order_detail':
                $id = input('id');
                $order = $this->order_model->get_order_by_id($id);
                $order['add_time'] =(empty($order['add_time'])?'':date('Y-m-d H:i:s',$order['add_time']));
                $order['paid_time'] =(empty($order['paid_time'])?'未支付':date('Y-m-d H:i:s',$order['paid_time']));
                $order['send_time'] = (empty($order['send_time'])?'未发货':date('Y-m-d H:i:s',$order['send_time']));
                $order['recv_time'] = (empty($order['recv_time'])?'未收货':date('Y-m-d H:i:s',$order['recv_time']));
                $builder       = new AdminConfigBuilder();
                $builder
                    ->title('订单详情')
                    ->keyReadOnly('id','订单id')
					->keytext('pay_name','买家')
					->keyText('mobile','联系方式')
					->keyText('goods_price','订单价格')
                    ->keytext('add_time','创建时间')
                ;
                $product_input_list = array(
                    'goods_name'=>array('name'=>'商品名','type'=>'text'),
                    'goods_num'=>array('name'=>'数量','type'=>'text'),
                    'goods_price'=>array('name'=>'价格/分','type'=>'text'),
                    'spec_key_name'=>array('name'=>'规格','type'=>'text'),
//					'main_img'=>array('name'=>'商品主图','type'=>'SingleImage')
                );
                if(!empty($order['goods']))
                {
                    foreach($order['goods'] as $pk=> $product)
                    {
                        $MultiInput_name='|';
                        foreach($product_input_list as $k=>$kv)
                        {
                            $name = 'porduct'.$pk.$k;
                            if($k == 'sku_id')
                            {
                                if($product['sku_id'] = explode(';',$product['sku_id']))
                                {
                                    unset($product['sku_id'][0]);
                                    $order[$name] =(empty($product['sku_id'])?'无':implode(',',$product['sku_id'])) ;
                                }
                            }
                            else
                            {
                                $order[$name] = $product[$k];
                            }
                            $order[$name.'title'] = $kv['name'];
//							$builder->$kv['type']($name,$kv['name']);
                            $MultiInput_name .= $name.'title'.'|'.$name.'|';
                            $MultiInput_array[] =array('type'=>$kv['type'],'style'=>'width:95px;margin-right:5px','placeholder'=>'') ;
                            $MultiInput_array[] =array('type'=>$kv['type'],'style'=>'width:295px;margin-right:5px','placeholder'=>'') ;
                        }
                        $builder->keyMultiInput(trim($MultiInput_name,'|'),'商品['.($pk+1).']信息','',$MultiInput_array);

                    }
                }
                return $builder
                    ->keytext('paid_time','支付时间')
                    ->keyMultiInput('goods_price|coupon_price|shipping_price','支付信息(单位：分)','支付金额|优惠金额|运费',array(
                        array('type'=>'text','style'=>'width:95px;margin-right:5px','placeholder'=>''),
                        array('type'=>'text','style'=>'width:95px;margin-right:5px','placeholder'=>''),
                        array('type'=>'text','style'=>'width:95px;margin-right:5px','placeholder'=>''),
                    ))
                    ->keyText('send_time','发货时间')
                    ->keyText('recv_time','收货时间')
                    ->buttonSubmit('')
                    ->buttonBack()
                    ->data($order)
                    ->fetch();
                break;
            case 'edit_order_modal':
                if(request()->isPost())
                {
                    $order_id = input('order_id','','intval');
                    $status = input('order_status','','intval');
                    $order = $this->order_model->get_order_by_id($order_id);
                    if(empty($order_id) || empty($status) || !($order))
                    {
                        $this->error('参数错误');
                    }
                    else
                    {
                        switch ($status)
                        {
                            case '1':
                                //取消订单
                                $ret = $this->order_logic->cancal_order($order);
                                if($ret)
                                {
                                    $this->success('操作成功');
                                }
                                else
                                {
                                    $this->error('操作失败,'.$this->order_logic->error_str);
                                }
                                break;
                            case '2':
                                //发货
                                $courier_no = input('courier_no');
                                $courier_name = input('courier_name');
                                $courier_phone = input('courier_phone','','intval');
                                $delivery_info = array(
                                    'courier_no'=>$courier_no,
                                    'courier_name'=>$courier_name,
                                    'courier_phone'=>$courier_phone,
                                );
                                $ret = $this->order_logic->send_good($order,$delivery_info);
                                if($ret)
                                {
                                    $this->success('操作成功');
                                }
                                else
                                {
                                    $this->error('操作失败,'.$this->order_logic->error_str);
                                }
                                break;
                            case '3':
                                //确认收货
                                $ret = $this->order_logic->recv_goods($order);
                                if($ret)
                                {
                                    $this->success('操作成功');
                                }
                                else
                                {
                                    $this->error('操作失败,'.$this->order_logic->error_str);
                                }
                                break;
                            case '8':
                                //拒绝退款
                                $refund_reason = input('refund_reason','');
                                $this->error('暂不支持该操作,'.$this->order_logic->error_str);
                                break;
                            case '10':
                                //删除订单
                                $ret = $this->order_logic->delete_order($order['id']);
                                if($ret)
                                {
                                    $this->success('操作成功');
                                }
                                else
                                {
                                    $this->error('操作失败,'.$this->order_logic->error_str);
                                }
                                break;
                        }

                    }
                }
                else{
                    $id = input('id');                        //获取点击的ids
                    $order = $this->order_model->get_order_by_id($id);
                    $this->assign('order', $order);
                    return $this->fetch('edit_order_modal');
                }


                break;
            case 'confirm':
                $ids = input('ids/a');
                $ret = 0;
                foreach ($ids as $id){
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
                }
                if($ret)
                {
                    return ['info'=>'操作成功','url'=>url('order')];
                }
                else
                {
                    return ['info'=>'操作失败','url'=>url('order')];
                }
                break;
            default:

                $option['page'] = input('page',1);
                $option['r'] = input('r',10);
                $option['user_id'] = input('user_id');
                $option['pay_status'] = input('status');
                $is_pay = input('get.status',0,'intval');
                $is_send = input('get.send',0,'intval');
                $is_pay -= 1;
                $is_send -= 1;

                $string = '';
                $user = input('id') ;//? input('id') : session('user')   ;

                $goods = input('key')  ;//? input('goods') : session('goods');
                $owner = input('user');

                $gstring =" goods_name like '%".$goods."%' and user_name like '%".$user."%'";
                $user && $string .=" and ";
                ($is_pay != -1) && $string .="is_pay = ".$is_pay." and ";
                ($is_send != -1) && $string .="is_send = {$is_send} and ";

                if ($owner == ''){
                    $ownerStr = '';
                }else{
                    $owners = db('Ucuser')->where('name','like','%'.$owner.'%')->find();
                    if ($owners)
                        $owner = $owners['mid'];
                        $ownerStr = 'or owner_id='.$owner;
                }

                if ($user == ''){
                    $user = '--';
                }
                if ($goods == ''){
                    $goods = '--';
                }



                $ordergoods = new OrderGoods();
                if ($is_pay != -1  || $is_send != -1 ){
                    $order  = db('OrderGoods')->where($string.$gstring)->order('id','desc')->select();
                    $count = $ordergoods->where("is_pay = {$is_pay} or is_send = {$is_send}")->count();
                    $option['r'] = $count;
                }elseif($user != '--' || $goods != '--'  ){
                    $order  = db('OrderGoods')->where("user_name like '%".$user."%' or goods_name like '%".$goods."%' ".$ownerStr)->order('id','desc')->select();
                    $count = $ordergoods->where("user_name like '%".$user."%' or goods_name like '%".$goods."%' ".$ownerStr)->count();
                    $option['r'] = $count;
                }else{
                    $order  = db('OrderGoods')->page($option['page'])->limit(10)->order('id','desc')->select();
                    $count = db('OrderGoods')->count();
                }



                $builder = new AdminListBuilder();
                $builder
                    ->title('订单管理')
                    ->setSearchPostUrl(url('shop/order'))
                    ->search('', 'id', 'text', '用户名', '', '', '')
                    ->search('', 'key', 'text', '商品名', '', '', '')
                    ->search('', 'user', 'text', '卖家姓名', '', '', '')
                    ->select('订单状态：', 'status', 'select', '', '', '', array(array('id'=>0,'value'=>'无状态'),array('id'=>1,'value'=>'待付款'),array('id'=>2,'value'=>'已付款'),array('id'=>4,'value'=>'待退款'),array('id'=>6,'value'=>'已退款')))
                    ->select('发货状态：', 'send', 'select', '', '', '', array(array('id'=>0,'value'=>'无状态'),array('id'=>1,'value'=>'待发货'),array('id'=>2,'value'=>'已发货'),array('id'=>3,'value'=>'已取货')))
//                    ->select('显示模式:', 'show_type', 'select', '', '', '', $show_type_array)
//                    ->buttonNew(url('shop/order'), '全部订单')
                    ->ajaxButton(url('shop/order',array('action'=>'confirm')),array(),'确认收货')
                    ->ajaxButton(url('shop/order',array('action'=>'delete')),array(),'删除订单')
                    ->keyText('id','订单id')
                    ->keyText('goods_name','商品名字');
//                    ->keyJoin('user_id','用户','uid','nickname','ucuser','/admin/user/index');
//					->ajaxButton(url('shop/order',array('action'=>'delete')),'','删除')
//                $option['show_type'] && $builder
//                    ->keyTime('add_time','下单时间')
//                    ->keyTime('paid_time','支付时间')
//                    ->keyTime('send_time','发货时间')
//                    ->keyTime('recv_time','收货时间');

                $builder
                    ->keyMap('is_pay','订单状态',array(0=>'待付款',1=>'已付款',3=>'待退款',5=>'已退款',6=>'已完成'))
                    ->keyMap('is_send','发货状态',array(0=>'待发货',1=>'已发货',2=>'已取货'))
                    ->keyText('sum','总价/分')
                    ->keyText('mail_price','运费')
                    ->keyText('user_name','买家')
//                    ->keyBuyer('user_id','买家信息')
                    ->keyOwner('owner_id','卖家名字-微信-电话')
                    ->keyText('create_time','时间')
//                    ->keyShop('parent_sn','店铺')
//                    ->keyMap('order_status','订单状态',array('0'=>'正常','1'=>'取消',''));

//                $builder->keyDoAction('@shop/shop/order/action/order_detail/id/###','订单详情')
//                    ->keyDoAction('@shop/shop/order/action/order_address/id/###','收货地址')
//                    ->keyDoAction('@shop/shop/order/action/order_delivery/id/###','发货信息')
//                    ->keyDoAction('@shop/shop/order/action/edit_order_modal/id/###','订单操作','订单操作');

                    ->data($order)
                    ->pagination($count, $option['r']);
                    return $builder->fetch();
                break;
        }

    }

    /**
     * 退款申请
     */
    public function returnInfo($action=''){
        switch ($action){
            case 'delete':
                $ids = input('ids/a');
//                $ret = $this->order_logic->delete_order($ids);
                $ret = db('OrderGoods')->delete($ids);
                if($ret)
                {
                    return ['info'=>'删除成功','url'=>url('')];
                }
                else
                {
                    return ['info'=>'删除失败','url'=>url('')];
                }
                break;

            case 'return':
                $ids = input('ids/a');
                $msg = '退款成功';
                foreach ($ids as $id){
                    $msg = $this->redirect(url('@mpbase/wxapp/wxrefund',['id'=>$id,'mp_id'=>'026d56fe7b4db2e57c8209cf01aacc78']));
                }

                return ['info'=>$msg,'url'=>url('')];


                break;
            case 'order_detail':
                if (request()->isPost()){
                    $id = input('id');
                    $status = input('order_status');
                    db('shop_order')->where("id = $id")->setField(['order_status'=>$status]);
                    $ret = array(
                        'info'=>'操作成功',
                        'url' => url('shop/shop/returnInfo')
                    );
                    return $ret;
                }else{
                    $id = input('id');
                    $order = $this->order_model->get_order_by_id($id);
                    $order['add_time'] =(empty($order['add_time'])?'':date('Y-m-d H:i:s',$order['add_time']));
                    $order['paid_time'] =(empty($order['paid_time'])?'未支付':date('Y-m-d H:i:s',$order['paid_time']));
                    $order['send_time'] = (empty($order['send_time'])?'未发货':date('Y-m-d H:i:s',$order['send_time']));
                    $order['recv_time'] = (empty($order['recv_time'])?'未收货':date('Y-m-d H:i:s',$order['recv_time']));
                    $builder       = new AdminConfigBuilder();

                    $status_select = $this->order_model->get_order_status_config_select();
                    $builder
                        ->title('订单详情')
                        ->keyReadOnly('id','订单id')
                        ->keytext('add_time','创建时间')
                        ->keytext('pay_name','买家')
                        ->keytext('mobile','手机')
                    ;
                    $builder
                        ->keytext('paid_time','支付时间')
                        ->keytext('goods_price','支付金额')
                        ->keySelect('order_status','状态','',$status_select )
                        ->buttonSubmit(url('shop/returnInfo',array('action'=>'order_detail')))
                        ->buttonBack()
                        ->data($order);
                    return $builder->fetch();
                }

                break;
            default:
                $option['page'] = input('page',1);
                $option['r'] = input('r',10);
                $option['user_id'] = input('user_id');
                $option['order_status'] = input('status');
                $option['key'] = input('key');
                $option['ids'] = input('id');
                //退款条件
//
                $user = input('id','');
                $goods = input('key','');
                $order['is_pay'] = input('status');
                $order['is_send'] = input('send');
                $order['user_name'] = array('like','%'.$user.'%');
                $order['goods_name'] = array('like','%'.$goods.'%');
                $order  = db('OrderGoods')->where('is_pay =3')->limit(($option['page']-1)*$option['r'],$option['r'])->order('id','desc')->select();
                $count = db('OrderGoods')->where('is_pay =3')->count();


                $builder = new AdminListBuilder();
                $builder
                    ->title('退款管理')
//                    ->select('订单状态：', 'order_status', 'select', '', '', '', $status_select2)
//
                    ->buttonNew(url('shop/returnInfo'), '全部订单')
                    ->ajaxButton(url('shop/returnInfo',array('action'=>'return')),array(),'退款')
//                    ->ajaxButton(url('shop/returnInfo',array('action'=>'delete')),array(),'删除')
                    ->keyText('id','订单id')
//                    ->keyJoin('goods_id','商品','id','goods_name','shop_goods')
                    ->keyText('goods_name','商品')
                    ->keyText('user_name','用户')
                    ->keyText('sum','金额')
//                $option['show_type'] && $builder
                    ->keyText('pay_time','支付时间')
                    ->keyText('rec_time','退款时间')
                    ->keyMap('is_pay','支付状态',array(3=>'待退款',5=>'已退款'));

//                $option['show_type'] || $builder
//                    ->keyMap('order_status','订单状态',$status_select)
//                    ->keyText('goods_price','总价/分')
//                    ->keyDoAction('@shop/shop/returnInfo/action/order_detail/id/###','详情');
                $builder
                    ->data($order)
                    ->pagination($count, $option['r']);
                return $builder->fetch();
                break;
        }
    }


    //统计
    public function checkout($action=''){
        switch ($action){
            case 'update':
                $ids = input('ids/a');
                $ret = db('OrderGoods')->where('id','in',$ids)->setField(['checkout'=>1]);
                if($ret)
                {
                    return ['info'=>'操作成功','url'=>url('')];
                }
                else
                {
                    return ['info'=>'操作失败','url'=>url('')];
                }
                break;
            case 'delete':
                $ids = input('ids/a');
                $ret = db('OrderGoods')->delete($ids);
                if($ret)
                {
                    return ['info'=>'删除成功','url'=>url('')];
                }
                else
                {
                    return ['info'=>'删除失败','url'=>url('')];
                }
                break;
            default:
                $option['page'] = input('page',1);
                $option['r'] = input('r',10);

                $order = array();

                //帅选
                $check = input('get.check');

                $checkout = '';
                if($check){
                    $order['checkout'] = $check-1;
                    $checkout = ' and checkout ='.($check-1);
                }

                //搜索
                $goods = input('key','');
                $user = input('user','');

                if ($user && $user != ''){
                    $user = db('Ucuser')->where('name','like','%'.$user.'%')->find();
                    if ($user)
                        $order['owner_id'] = $user['mid'];//array('like','%'.$user['nickname'].'%');
                }
                if ($goods && $goods!= '')
                    $order['goods_name'] = array('like','%'.$goods.'%');


                //条件
                $order['is_pay']=1;
                $order['is_send'] = 2;
                $per_count = 10;


                $db_prefix=config('table_prefix');//表前缀
                //根据商品名
                if(isset($goods) && $goods!= ''){
                    $order  = db('OrderGoods')->where($order)->order('id','desc')->select();

                    $count = count($order);

//                    增加统计数据
                    $sum = Db::query("select '总价' as id,'商品汇总' as goods_name,is_pay,is_send,sum(sum) as sum,owner_id,'下单' AS create_time,'支付时间' as pay_time,'收货时间' as rec_time,checkout from uctoo_order_goods where (is_pay = 1 and is_send = 2 ".$checkout." and goods_name like '%".$goods."%') group by goods_name");

                    if($sum){

                        $order[$count] = $sum[0];
                        $count +=1;
                        $per_count = $count;
                    }
                    //跟据用户名
                }elseif (isset($user) && $user != ''){
                $order  = db('OrderGoods')->where($order)->order('id','desc')->select();

                $count = count($order);

//                    增加统计数据
                $sum = Db::query("select '总价' as id,'商品汇总' as goods_name,is_pay,is_send,sum(sum) as sum,owner_id,'下单' AS create_time,'支付时间' as pay_time,'收货时间' as rec_time,checkout from uctoo_order_goods where (is_pay = 1 and is_send = 2 ".$checkout." and owner_id = {$user['mid']}) group by owner_id");

                if($sum){

                    $order[$count] = $sum[0];
                    $count +=1;
                    $per_count = $count;
                }
            }else{
                    unset($order['goods_name']);
                    unset($order['owner_id']);
                    $string = 'is_pay = 1 and is_send = 2';

                    if ($checkout)
                        $string .= $checkout;
                    $order  = db('OrderGoods')->where($order)->limit(($option['page']-1)*$option['r'],$option['r'])->order('id','desc')->select();

                    $count = db('OrderGoods')->where($string)->count();


                }



                $builder = new AdminListBuilder();
                $builder
                    ->title('订单结算')
                    ->setSearchPostUrl(url('shop/checkout'))
//                    ->setSelectPostUrl(url('shop/checkout',array('user'=>$user,'key')))
                    ->select('状态：', 'check', 'select', '', '', '', array(array('id'=>0,'value'=>'无状态'),array('id'=>1,'value'=>'未结算'),array('id'=>2,'value'=>'已结算')))
                    ->search('', 'key', 'text', '商品名', '', '', '')
                    ->search('', 'user', 'text', '卖家姓名', '', '', '')
                    ->buttonNew(url(),'显示全部')
                    ->ajaxButton(url('shop/checkout',array('action'=>'update')),array(),'完成结算')
//                    ->ajaxButton(url('shop/checkout',array('action'=>'delete')),array(),'删除订单')
                    ->keyText('id','订单id')
                    ->keyText('goods_name','商品名字');
                 $builder
                    ->keyMap('is_pay','订单状态',array(0=>'待付款',1=>'已付款'))
                    ->keyMap('is_send','发货状态',array(0=>'待发货',1=>'已发货',2=>'已取货'))
                    ->keyText('sum','总价/分')
                    ->keyOwner('owner_id','卖家名字-微信-电话')
                     ->keyText('create_time','下单时间')
                     ->keyText('pay_time','支付时间')
                     ->keyText('rec_time','收货时间')
//                    ->keyWechat('owner_id','卖家微信')
                    ->keyMap('checkout','发货状态',array(0=>'未结算',1=>'已结算'))
                    ->data($order)
                    ->pagination($count, $per_count);
                return $builder->fetch();
                break;
        }
    }
    /*
	 * 运费模板
	 */
    public function delivery1($action = '')
    {
        switch($action)
        {
            case 'add':
                if(request()->isPost())
                {
                    $delivery = input('post.');
                    if (!$delivery){

                        $this->error($this->delivery_model->getError());
                    }
//
                    $ret = $this->delivery_model->add_or_edit_delivery($delivery);
                    if ($ret)
                    {
                        return ['info'=>'操作成功。', 'url'=>url('shop/delivery')];
                    }
                    else
                    {
                        return ['info'=>'操作失败。', 'url'=>url('shop/delivery/action/add')];
                    }
                }
                else
                {
                    $builder       = new AdminConfigBuilder();
                    $id = input('id');
                    if(!empty($id))
                    {
                        $delivery = $this->delivery_model->get_delivery_by_id($id);
                    }
                    else
                    {
                        $delivery = array();
                    }

					$builder->title('新增/修改运费模板')
						->keyId()
						->keyText('title', '模板名称')
						->keyRadio('valuation','快递方式','',array(0=>'包邮',1=>'快递',2=>'平邮'))
						->keyText('price','邮费')
                        ->keyTextArea('brief', '模板说明')
						->keyCreateTime()
						->data($delivery)
						->buttonSubmit(url('shop/delivery',array('action'=>'add')))
						->buttonBack();
					return $builder->fetch();
                }
                break;
            case 'delete':
                $ids = input('ids/a');
                $ret = $this->delivery_model->delete_delivery($ids);
                if ($ret)
                {
                    return ['info'=>'删除成功。', 'url'=>url('shop/delivery')];
                }
                else
                {
                    return ['info'=>'操作失败。'];
                }
                break;
            default:
                $option['page'] = input('page',1);
                $option['r'] = input('r',10);
                $delivery = $this->delivery_model->get_delivery_list($option);
                $totalCount = $delivery['count'];

                $builder = new AdminListBuilder();
                $builder
                    ->title('运费模板管理')
                    ->buttonnew(url('shop/delivery',array('action'=>'add')),'新增运费模板')
                    ->ajaxButton(url('shop/delivery',array('action'=>'delete')),array(),'删除')
                    ->keyText('id','id')
                    ->keyText('title','标题')
                    ->keyText('brief','模板说明')
//					->keyMap('valuation','计费方式',array())
                    ->keyTime('create_time','创建时间')
                    ->keyDoAction('@shop/shop/delivery/action/add/id/###','编辑')
                    ->data($delivery['list'])
                    ->pagination($totalCount, $option['r']);
                    return $builder->fetch();
                break;
        }
    }

    //关于我们
    public function about(){
        if (request()->isPost()){
            $data = input('post.');
            $ret = $this->product_model->edit_about($data);
            if($ret){
                return ['status'=>true,'info'=>'编辑成功'];
            }else{
                return ['status'=>true,'info'=>'编辑失败'];
            }
        }else{
            $ret = $this->product_model->get_about_by_id(1);
            $this->assign('list',$ret);
            return $this->fetch();
        }

    }

    /*
     * 价格管理设置
    */
    public function price($action = ''){
        switch ($action){
            case 'add':
                $cate = $this->product_cats_model->where('parent_id <> 0')->select();
                $this->assign("tag",$cate);
                if(request()->isPost()){
                    $data = input('post.');
                    if (isset($data)){
                        $ret = $this->product_model->add_or_edit_price($data);
                    }
                    if ($ret){
                        return ['status'=>true,'info'=>'编辑成功','url'=>url('shop/price')];
                    }else{
                        return ['status'=>true,'info'=>'编辑失败'];
                    }
                }else{
                    $id = input('id');
                    if (!empty($id)){
                        $list = $this->product_model->get_price_by_id($id);
                        $list = $list[0];
                        $this->assign('list',$list);
                    }
                    return $this->fetch('price_add');
                }
                break;
            case 'delete':
                $ids = input('ids');
                $ret = $this->product_model->delete_price($ids);
                if ($ret)
                {

                    return ['status'=>true,'info'=>'操作成功。','url'=>url('shop/price')];
                }
                else
                {
                    return ['status'=>true,'info'=>'操作失败。'];
                }
                break;
            default:
                $ret = $this->product_model->get_price_by_select();
                $this->assign('list',$ret);
                return $this->fetch();
        }

    }


    /**
     * 优惠券功能
     */
    function coupons($action = ''){
        switch ($action){
            case 'add':
                if(request()->isPost()){
                    $data = input('post.');
//                    $couponModel = model('ShopCoupon');
                    $num = $data['publish_cnt'];
                    unset($data['publish_cnt']);
                    $dataArr = array();
//                    ini_set('precision', 18);
                    for($i = 0; $i < $num;$i++){
//                        $dataArr[$i] = array(
//                            'title'=>'','price'=>0,'create_time'=>'','deadline'=>'','limit'=>'','type'=>''
//                        );
                        $time = explode('.',microtime(true));
                        $data['coupons_id'] = $time[0].mt_rand(1,15).$time[1].mt_rand(1,15).mt_rand(1,10);
//                        trace($data['title'],'dataaaaa');
//                        $dateArr[$i]['title'] = $title;
//                        $dateArr[$i]['price'] = $data['price'];
//                        $dateArr[$i]['create_time'] = $data['create_time'];
//                        $dateArr[$i]['deadline'] = $data['deadline'];
//                        $dateArr[$i]['limit'] = $data['limit'];
//                        $dateArr[$i]['type'] = $data['type'];

                        $dataArr[$i] = $data;

                    }
                    trace($dataArr,'z数组');
//                    $couponModel->allowField(true)->saveAll($data);
                    $ret = db('shop_coupon')->insertAll($dataArr);
                    if($ret)
                    {
                        return ['info'=>'操作成功','url'=>url('shop/coupons')];
                    }
                    else
                    {
                        return ['info'=>'操作失败','url'=>url('')];
                    }
                }else{
                    $id = input('id');
                    if (!empty($id)){
                        $data = db('shop_coupon')->find($id);
                    }else{
                        $data = array();
                    }
                    $builder = new AdminConfigBuilder();
                    $builder->title('添加优惠券')
                        ->keyId()
                        ->keyText('title','名称')
                        ->keyText('price','优惠金额')
                        ->keyText('limit','高于多少钱可用','整数')
                        ->keyText('publish_cnt','发布数量')
                        ->keyTime('create_time','开始时间')
                        ->keyTime('deadline','到期时间')
                        ->keyRadio('type','类型','',array(0=>'全场商品',1=>'邮费券'))
                        ->data($data)
                        ->buttonSubmit(url('shop/coupons',array('action'=>'add')))
                        ->buttonBack();
                    return $builder->fetch();
                }

                break;
            case 'delete':
                $ids = input('ids/a');
                $ret = db('shop_coupon')->delete($ids);
                if($ret)
                {
                    return ['info'=>'删除成功','url'=>url('')];
                }
                else
                {
                    return ['info'=>'删除失败','url'=>url('')];
                }
                break;
            default:
                $data = db('shop_coupon')->select();
                $total = count($data);
                $builder = new AdminListBuilder();
                $builder->title('优惠券列表')
                    ->buttonNew(url('shop/coupons',array('action'=>'add')),'添加优惠券')
                    ->ajaxButton(url('shop/coupons',array('action'=>'delete')),array(),'删除')
                    ->keyId()
                    ->keyText('title','优惠券名称')
                    ->keyText('price','价格')
                    ->keyText('limit','高于多少可用')
                    ->keyTime('create_time','起始时间')
                    ->keyTime('deadline','到期时间')
                    ->keyMap('type','类型',array(0=>'全场商品',1=>'邮费券'))
                    ->keyMap('status','类型',array(0=>'未使用',1=>'已使用',2=>'已过期'))
                    ->keyDoAction('@shop/shop/coupons/action/add/id/###','编辑')
                    ->data($data)
                    ->pagination($total,10);
                return $builder->fetch();
        }
    }

//	上传文件
    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $path = 'uploads/picture/';
        $info = $file->rule('uniqid')->move($path);
        if($info){
            // 成功上传后 获取上传信息

            $data= array(
                'status'=>1,
                'type'=>$info->getExtension(),//获取类型
                'path'=>'/uploads/picture/'.$info->getFilename(),
                'url'=>request()->domain(). '/uploads/picture/'.$info->getFilename(),
                'create_time'=>time(),
            );
            $data['id'] = Db::name('picture')->insertGetId($data);
            if ($data['id']){
                echo json_encode($data);//var_dump($info);
                exit();
            }

        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }


}
