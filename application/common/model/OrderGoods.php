<?php
/**
 * Created by PhpStorm.
 * User: UCT
 * Date: 2017/2/22
 * Time: 16:56
 */
namespace app\common\model;
use think\Model;

class OrderGoods extends Model{

    //定义时间戳
//    protected $autoWriteTimestamp = true;
//    protected $updateTime = 'last_update';
//    protected $createTime = 'on_time';

    function posts($post){
        if (!empty($post)){

            $owner = db('ShopGoods')->where('id',$post['goods_id'])->column('user_id');
            $post['owner_id'] = $owner[0];
            $post['order_sn'] = time().time();
            $post['create_time'] = date('Ymd',time());
            $ret = $this->allowField(true)->save($post);
        }
        return $ret ? $ret : 0;
    }

    function lists($where){
        if (empty($where)) {
            $where = array('is_on_sale' =>0);
        }else{
            $where = array_merge(array('is_on_sale' =>0),$where);
        }
        return $this->where($where)->order('sort,last_update desc')->select();
    }

    function edit($update){
        if (!empty($update)){
            $ret = $this->allowField(true)->save($update,['id'=>$update['id']]);
        }
        return $ret ? $ret : 0;
    }

    function add_or_edit($data){
        if (empty($data['id'])){
            $ret = $this->allowField(true)->save($data);
        }else{
            $ret = $this->allowField(true)->save($data,['d'=>$data['id']]);
        }
        return $ret;
    }

    function get_lists($id=null){
        if (empty($id)){
            trace('定位这里');
            return $this->select();
        }
        return $this->where('id',$id)->find();
    }
}