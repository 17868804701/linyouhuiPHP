<?php

namespace app\api\controller;

use think\Controller;
use think\Request;

class District extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        //分页
        $page = input('page',1);
        $limit = input('limit',20);
        //排序
        $sortby = input('sortby','id');
        $sort = input('sort','desc');
        //查找条件
        $param = input('param.');
        //清除多余条件
        foreach($param as $k => $v){
            if($v == '') unset ($param[$k]);
        }
        unset($param['page']);
        unset($param['limit']);
        unset($param['sortby']);
        unset($param['sort']);

        //用order表演示数据
        $list = model('district')->where($param)->order($sortby,$sort)
            ->limit($limit)->page($page)
            ->select();
        return json($list);
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

    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id='')
    {

    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id='')
    {

    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id='')
    {

    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id='')
    {

    }
}
