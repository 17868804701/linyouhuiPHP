<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\shop\model\ShopProduct as ShopProductModel;

class ShopProduct extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        //
        trace($request,'ShopProduct');
        $limit = input('limit',20);
        $page = input('page',1);
        $param = input('param.');
        trace($param,'$oparam');
        foreach( $param as $k=>$v) {           //整理参数
            if('' == $v) unset($param[$k]);
        }
        unset($param['page']);
        unset($param['limit']);
        $param['status']=0;//状态判断
        $oModel = new ShopProductModel();
        $item  = $oModel->where($param)->find();
//        $item = str_replace("<br>","\n",$oData);

        $item['detile1'] = strip_tags($item['detile1']);
        if (!empty($item['detile2']))
            $item['detile2'] = strip_tags($item['detile2']);
        if (!empty($item['detile3']))
            $item['detile3'] = strip_tags($item['detile3']);
        if (!empty($item['detile4']))
            $item['detile4'] = strip_tags($item['detile4']);


        trace($item,'item');
        return json($item);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
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
        //
    }
}
