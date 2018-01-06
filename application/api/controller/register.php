<?php
/**
 * Created by PhpStorm.
 * User: uctoo
 * Date: 2017/2/9
 * Time: 10:28
 */
namespace app\api\controller;

use think\Controller;
use think\Request;

class register extends Controller{
    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = input('param.');
        foreach( $data as $k=>$v) {           //整理参数
            if('' == $v) unset($data[$k]);
        }

        trace($data,'提交数据');
        $oModel = model("ucuser");
        $ret = $oModel->add_or_edit_order($data);
        $oData = $oModel->where("order_id",$ret)->find();
        return json($oData);
    }
}
