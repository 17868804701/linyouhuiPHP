<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\shop\model\ShopProductCats as ShopProductCatsModel;

class ShopProductCats extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {

        $parent_id = input('get.parent_id');
        $limit = input('limit',50);
        $page = input('page',1);
        trace($parent_id,'parent_id');
        $param = input('param.');
        trace($param,'$oparam');
        foreach( $param as $k=>$v) {           //整理参数
            if('' == $v) unset($param[$k]);
        }
        unset($param['page']);
        unset($param['limit']);
        $param['status']=0;//状态判断

//        trace($param,'$param');
        trace($param,'$oparam');
        $oModel = new ShopProductCatsModel();
        $oData = $oModel->where($param)->limit($limit)->page($page)
            ->order('sort desc', ' create_time')
            ->select();
        foreach ($oData as $row){
            $row['image'] = get_cover($row['image'],'url');
        }
        return json($oData);
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
