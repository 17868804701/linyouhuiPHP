<?php
/**
 * Created by PhpStorm.
 * User: UCT
 * Date: 2017/2/22
 * Time: 16:56
 */
namespace app\common\model;
use think\Model;

class ShopOrder extends Model{


    //定义时间戳
    protected $autoWriteTimestamp = false;
    protected $updateTime = false;
    protected $createTime = 'add_time';


    function Posts($post){
        if (!empty($post)){
            $openid = db('ucuser')->where('mid',$post['user_id'])->column('openid');


            $post['order_sn'] = $post['user_id'].time().rand(10,99);
            $post['pay_code'] = $openid[0];
            $post['add_time'] = time();
            trace($post,'提交数据');
            $ret = $this->allowField(true)->save($post);
            if ($ret){
                return $this;
            }
        }

        return  0;
    }

    function order_lists($where){
        if (!empty($where)){
            $where = array_merge(array('is_on_sale'=>1),$where);
            $ret = $this->where($where)->order('sort,last_update desc')->select();
        }else{
            $ret = $this->where(array('is_on_sale'=>1))->order('sort,last_update desc')->select();
        }
        return $ret;
    }

    function edit($update){
        if (!empty($update)){
            $ret = $this->allowField(true)->save($update,['goods_id'=>$update['goods_id']]);
        }
        return $ret ? $ret : 0;
    }
}