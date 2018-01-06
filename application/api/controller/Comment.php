<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\shop\model\Shop;
use app\common\model\OrderGoods;

class Comment extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $id = input('goods_id');
        $parent = input('parent_id');
        if($id){
            $comm = db('ShopComment')->where('goods_id',$id)->order('id','desc')->select();
        }else{

            $comm = db('ShopComment')->where('parent_id',$parent)->order('id','desc')->select();
        }
        //spec


        return json($comm);
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
        $data = input('post.');
        unset($data['id']);
        $id = input('id');
        $goods_id = input('goods_id');


        $item = OrderGoods::where('goods_id',$goods_id)->find();
        $data['parent_id'] = $item['owner_id'];
        $data['create_time'] = date('Ymd',time());
        $user = db('Ucuser')->find($data['user_id']);
        $data['images'] = $user['headimgurl'];
        $data['name'] = $user['nickname'];
        $data['goods_name'] = $item['goods_name'];
        $ret = db('ShopComment')->insertGetId($data);
        unset($data);
        OrderGoods::where('id',$id)->setField(['is_comment'=>1]);

        $data = array();
        $data['user_id'] = $item['user_id']; //缓过来
        $data['user_name'] = $item['user_name']; //缓过来
        $data['owner_id'] = $item['owner_id'];
        $data['goods_id'] = $item['goods_id'];
        $data['goods_name'] = $item['goods_name'];
        $data['create_time'] = time();
        $data['brief'] = $item['user_name'].'-买家发布评价';
        $data['type'] = 3;//上架
        $ret = db('ShopMessages')->insert($data);
        return $ret;
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
        $param = input('post.');
        trace($param,'提交了什么');
        $param['goods_id'] = $id;
        foreach( $param as $k=>$v) {           //整理参数
            if('' == $v) unset($param[$k]);
        }
        $ret = model('shop_goods')->edit($param);

        if ($ret){
            return  json(array('status'=>1,'msg'=>'添加成功'));
        }else{
            return  json(array('status'=>0,'msg'=>'添加失败'));
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $ret = model('shop_goods')->where(['id'=>$id])->delete();
        db('SpecGoodsPrice')->where('goods_id',$id)->delete();

        if ($ret){
            return  json(array('status'=>1,'msg'=>'删除成功'));
        }else{
            return  json(array('status'=>0,'msg'=>'删除失败'));
        }
    }
}
