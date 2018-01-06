<?php

namespace app\api\controller;

use app\common\model\ShopGoods;
use think\Controller;
use think\Request;
use app\shop\model\Shop;

class Goods extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $id = input('param.');
        $ids = input('id');
        trace($ids,'ID');
        foreach ($id as $key => $value) {
            if ($value == '') {
                unset($id[$key]);
            }
        }
        if (isset($ids)){
            $list = db('ShopGoods')->where('id',$ids)->select();
            foreach($list as &$item){
                //yonghu
                $user = db("Ucuser")->find($item['user_id']);
                if ($user){
                    $item['user'] = $user['nickname'];
                    $item['avatar'] = $user['headimgurl'];
                }

                //相册
                if (!is_null($item['image'])){
                    $item['image'] = get_cover($item['image'],'url');
                }
                if ($item['goods_img'] != ''){
                    $alb = explode(',',$item['goods_img']);
                    foreach ($alb as $a){


                        $album[] = get_cover($a,'url');
                    }
                    $item['goods_img'] = $album;
                }
            }

            return json($list);
        }

        $where = 'is_on_sale =0';
        //最热商品
        if (isset($id['hot'])){
            $shop_id = $id['shop_id'];
            $num = $id['hot'];
            if ($num == 1){
                $list = db('ShopGoods')->where("shop_id = $shop_id and is_on_sale =0")->order('sales_sum','desc')->select();
            }elseif($num == 2){
                $list = db('ShopGoods')->where("shop_id = $shop_id and is_on_sale =0")->order('goods_price','asc')->select();
            }elseif($num == 3){
                $list = db('ShopGoods')->where("shop_id = $shop_id and is_on_sale =0")->order('goods_price','desc')->select();
            }


        }else{

                //首页
            if (!empty($id)){
                $list =  model('shop_goods')->Lists($id);
            }else{
                $list =  model('shop_goods')->where('is_on_sale =0')->order('sort,on_time', 'desc')->select();
            }

        }



        foreach($list as &$item){
            //yonghu
            $user = db("Ucuser")->find($item['user_id']);
            if ($user){
                $item['user'] = $user['nickname'];
                $item['avatar'] = $user['headimgurl'];
            }

            //相册
            if (!is_null($item['image'])){
                $item['image'] = get_cover($item['image'],'url');
            }
            if ($item['goods_img'] != ''){
                $alb = explode(',',$item['goods_img']);
                foreach ($alb as $a){


                    $album[] = get_cover($a,'url');
                }
                $item['goods_img'] = $album;
            }
        }

        return json($list);
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

        //判断用户权限
        $userid = input('user_id');
        $shopid = input('shop_id');

        //提交数据
        $param = input('post.');
//        trace($param,'提交了什么');
        $where = array('id'=>$shopid,'shop_owner_id'=>$userid);
        if (db('shop')->where($where)->find()){
            trace('是管理员');
            $param['is_on_sale'] = 0;
            $flag = true;
        }else{
            trace('不是管理员');
            $param['is_on_sale'] = 1;
            $flag = false;
        }

        $file = request()->file('picture');

        //上传图片
        if ($file)
            $param['image'] = model('picture')->picture($file);
        //添加商品
        $shopGoods = new ShopGoods();
        $ret = $shopGoods->AddOrEdit($param);
        //发送审核信息
        if (!$flag){
            $goods = $shopGoods::get($ret);
            $shop = new Shop();
            $shop->CheckUpload($goods);
        }
        if ($ret){
            return  json(array('status'=>1,'msg'=>'添加成功'));
        }else{
            return  json(array('status'=>0,'msg'=>'添加失败'));
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
