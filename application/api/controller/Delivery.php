<?php

namespace app\api\controller;

use think\Controller;
use think\Request;

class Delivery extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $user_id = input('user_id');
        $shop_id = input('shop_id');
        $id = input('id');
        if (isset($id) && $id != ''){
            $ret = db('Delivery')->where('id in (' .$id. ')')->select();
        }elseif(isset($user_id) && isset($shop_id)) {
            $ret = db('Delivery')->where("user_id = $user_id")->select();
        }else {
            return null;
        }
        return json($ret);
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
            if ($data['id'] != 0){
                $ret = db('Delivery')->where('id',$data['id'])->update($data);
            }else{
                $ret = db('Delivery')->insert($data);
            }

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
        $ret = db('Delivery')->find($id);
        return json($ret);
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
        $ret = db('Delivery')->where('id',$id)->delete();

        if ($ret){
            return  json(array('status'=>1,'msg'=>'删除成功'));
        }else{
            return  json(array('status'=>0,'msg'=>'删除失败'));
        }
    }
}
