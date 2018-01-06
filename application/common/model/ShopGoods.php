<?php
/**
 * Created by PhpStorm.
 * User: UCT
 * Date: 2017/2/22
 * Time: 16:56
 */
namespace app\common\model;
use think\Model;

class ShopGoods extends Model{

    //定义时间戳
    protected $autoWriteTimestamp = true;
    protected $updateTime = 'last_update';
    protected $createTime = 'on_time';
    protected $insert = [
        'sort'=>0
    ];
//提交
    function post($post){
        if (!empty($post)){
            $ret = $this->allowField(true)->save($post);
            $ret = $this->id;
            db('Shop')->where('id',$ret)->setInc('shop_goods');
        }
        return $ret ? $ret : 0;
    }
//查找
    function Lists($where){
        if (empty($where)) {
            $where = array('is_on_sale' =>0);
        }else{
            $where = array_merge(array('is_on_sale' =>0),$where);
        }
        return $this->where($where)->order('sort','desc')->order('on_time','desc')->select();
    }

    function edit($update){
        if (!empty($update)){
            $ret = $this->allowField(true)->save($update,['id'=>$update['id']]);
        }
        return $ret ? $ret : 0;
    }
//上传商品
    function AddOrEdit($data){

        trace($data,'商品');
        if (empty($data['id'])){
            $ret = $this->allowField(true)->save($data);
            $goods_id = $this->id;
            trace($goods_id,'商品ID');
        }else{
            $ret = $this->allowField(true)->save($data,['id'=>$data['id']]);
            $goods_id = $data['id'];
        }
        // 商品规格价钱处理
        db("SpecGoodsPrice")->where('goods_id = '.$goods_id)->delete(); // 删除原有的价格规格对象


            $spec  = array();
                $spec['name1'] = $data['spec1'];
                $spec['name2'] = $data['spec2'];
                $spec['name3'] = $data['spec3'];

            $spec['goods_id'] = $goods_id;
            $spec['price1'] = $data['price1'];
            $spec['price2'] = $data['price2'];
            $spec['price3'] = $data['price3'];
            $spec['store1'] = $data['store1'];
            $spec['store2'] = $data['store2'];
            $spec['store3'] = $data['store3'];
            $spec = array_filter($spec);
            db('SpecGoodsPrice')->insert($spec);


        trace($goods_id,'商品ID');

        return $goods_id;
    }

    function EditGoods($data){

//        trace($data,'商品');
        if (empty($data['id'])){
            $ret = $this->allowField(true)->save($data);
            $goods_id = $this->id;
            trace($goods_id,'商品ID');
        }else{
            $ret = $this->allowField(true)->save($data,['id'=>$data['id']]);
            $goods_id = $data['id'];
        }
        // 商品规格价钱处理
        db("SpecGoodsPrice")->where('goods_id = '.$goods_id)->delete(); // 删除原有的价格规格对象


        $spec  = array();

            $spec['name1'] = $data['name1'];
            $spec['name2'] = $data['name2'];
            $spec['name3'] = $data['name3'];

        $spec['goods_id'] = $goods_id;
        $spec['price1'] = $data['price1'];
        $spec['price2'] = $data['price2'];
        $spec['price3'] = $data['price3'];
        $spec['store1'] = $data['store1'];
        $spec['store2'] = $data['store2'];
        $spec['store3'] = $data['store3'];
        $spec = array_filter($spec);
        db('SpecGoodsPrice')->insert($spec);


        trace($goods_id,'商品ID');

        return $goods_id;
    }

    function get_lists($id=null){
        if (empty($id)){
            trace('定位这里');
            return $this->select();
        }
        return $this->where('id',$id)->find();
    }
}