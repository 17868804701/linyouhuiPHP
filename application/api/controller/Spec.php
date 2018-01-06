<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\shop\model\Shop;

class Spec extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $id = input('param.id');
        //spec
        $spec = db('SpecGoodsPrice')->where('goods_id',$id)->find();

        $specArr = array();
        if ($spec){
            unset($spec['goods_id']);
            unset($spec['spec_id']);
            unset($spec['mp_id']);

            $specArr = array_chunk($spec,3);

            foreach ($specArr as $k => $value){    //过滤规格

                if ($value['name'.$k+1] == '') unset($specArr[$k]);
                if ($value['price'.$k+1] == '0.00') unset($specArr[$k]);
//                    else $specArr[] = $value;


            }
        }

        return json($specArr);
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
