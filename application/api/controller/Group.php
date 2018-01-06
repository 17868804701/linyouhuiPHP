<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\shop\model\Shop;


class Group extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $id = input('get.id');
        if ($id){
            $group = db('shop')->find($id);
            $add = array(
                $group['address1'],
                $group['address2'],
                $group['address3'],
                $group['address4']
            );
            $add = array_filter($add);
            $group['address'] = $add;
            $slides = array();
            foreach (explode(',',$group['shop_image']) as $val){
                $slides[] = get_cover($val,'url');
            }

            $group['slides'] = $slides;

        }else{
            $group = db('shop')->select();
            foreach ($group as &$g){
                $add = array(
                    $g['address1'],
                    $g['address2'],
                    $g['address3'],
                    $g['address4']
                );
                $add = array_filter($add);
                $g['address'] = $add;
                $slides = array();
                foreach (explode(',',$g['shop_image']) as $val){
                    $slides[] = get_cover($val,'url');
                }
                $g['slides'] = $slides;
            }
        }



        trace($group,'数组s');
        return json($group);
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
        return $ret;
    }
}
