<?php

namespace app\api\controller;

use think\Controller;
use think\Request;

class Address extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $id = input('id');
        $param = input('get.');
        foreach($param as $k => $v){
            if($v == '') unset ($param[$k]);
        }


        $msg = model('Address')->where($param)->select();
        return json($msg);
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
        $param = input('param.');
        //清除多余条件
        foreach($param as $k => $v){
            if($v == '') unset ($param[$k]);
        }
        unset($param['id']);
        $id = input('id');

        //更改默认
//        if (isset($param['default'])){
//            model('Address')->save(['default'=>0],['default'=>1]);
//            $add = model('Address')::get($param['id']);
//            $add->default = 1;
//            $add->save();
//            return $add;
//        }
//        //提交更新
//        $rst = db('Address')->where("user_id",$param['user_id'])->count();
//        if ($rst > 0){
//            $param['default'] = 0;
//        }else{
//            $param['default'] = 1;
//        }

        if (!$id ) {
            $ret = model('Address')->isUpdate(false)->allowField(true)->save($param);
        }else{
            $ret = model('Address')->isUpdate(true)->allowField(true)->save($param,['id'=>$id]);
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
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $ret = model('Message')->where('id',$id)->delete();
        abort();
        return $ret;
    }
}
