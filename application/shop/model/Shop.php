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
use app\common\model\Ucuser;
use app\common\model\Message;

class Shop extends Model{


//	protected $table = 'shop';
	protected $ucuser;
	protected $msg;


	function initialize()
    {
        $this->ucuser = new Ucuser();
        $this->msg = new Message();
    }

    /**
     * 验证用户是否属于小组
     * @param $user
     * @param $group
     * @return bool
     */
	function checkAuth($user,$group){
        $groups = db('user_group')->where(array('user_id'=>$user,'shop_id'=>$group))->find();
        if ($groups){
            return 1;
        }else{
            return 0;
        }
    }

    //申请入群
    function Apply($data){
        $data['type'] = 2;
        $data['create_time'] = time();
        $data['brief'] = '申请加入'.$data['shop_name'];
        $row = db('ShopMessages')->where(array('user_id'=>$data['user_id'],'owner_id'=>$data['owner_id'],'type'=>$data['type'],'status'=>0))->find();
        if ($row){
            return 0;
        }else{
            $ret = db('ShopMessages')->insert($data);
            return $ret;
        }
    }

    //创建新东西
    function create_shop($data){
        $user = $data['shop_owner_id'];
        if (empty($data['id'])){
            $ret = $this->allowField(true)->save($data);
        }else{
            $ret = $this->allowField(true)->isUpdate(true)->save($data);
        }
        $shop_id = $this->id;
        //添加群组到用户信息
        $this->ucuser->where('mid',$user)->update(['groupid'=>$shop_id]);
        return $ret;
    }

    //上传商品发送验证
    function CheckUpload($goods){
        $data = array();
        $data['user_id'] = $goods->user_id; //缓过来
        $data['shop_id'] = $goods->shop_id;
        $data['owner_id'] = $goods->owner_id;
        $data['goods_id'] = $goods->id;
        $data['goods_name'] = $goods->goods_name;
        $data['goods_img'] = get_cover($goods->image,'url');
        $data['user_name'] = $goods->user_name;
        $data['user_img'] = $goods->user_img;
        $data['create_time'] = time();
        $data['brief'] = $data['goods_name'].'申请上架';
        $data['type'] = 1;//上架
        db('ShopMessages')->insert($data);
    }
}

