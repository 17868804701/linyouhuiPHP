<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\shop\model\Shop;
use app\common\model\UserGroup;

class Check extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
//        $group = input('group');
//        $userid = input('user');
//        $owner = input('owner_id');
        return 1;
//        if ($userid == $owner){
//            return 1;
//        }else{
//            $shop = new Shop();
//            return  $shop->checkAuth($userid,$group); //关闭开关
//        }
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
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = input('post.');
        trace($data,'申请入群');
        if (empty($data['shop_id']))
            return false;
        $shop = new Shop();
        $ret = $shop->Apply($data);
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
        $data = array();
        $data['owner_id'] = input('owner_id'); //缓过来,用户ID
        $data['shop_id'] = input('shop_id');
        $data['user_id'] = input('user_id');
        $id = input('id');
        $data['shop_name'] = input('shop_name');
        $data['create_time'] = time();
        $data['brief'] = '入群申请已通过群主审核';
        $data['type'] = 3;//普通小学

        db('ShopMessages')->insert($data);
        //插入群组
        $data['shop_admin_id'] = $data['user_id'];
        $data['user_id'] = $data['owner_id'];

        //通过入群
        $ret = model('UserGroup')->allowField(true)->save($data);
        db('ShopMessages')->where('id',$id)->setField(['status'=>1]);

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
        $data = array();
        $data['owner_id'] = input('owner_id'); //
        $data['shop_id'] = input('shop_id');
        $data['user_id'] = input('user_id');
        $id = input('id');
        db('ShopMessages')->delete($id);
        $data['shop_name'] = input('shop_name');
        $data['create_time'] = time();
        $data['brief'] = $data['shop_name'].'入群申请被驳回';
        $data['type'] = 4;//普通小学
        db('ShopMessages')->where('id',$id)->delete();
        $ret = db('ShopMessages')->insert($data);
        return $ret;
    }
}
