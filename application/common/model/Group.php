<?php
/**
 * Created by PhpStorm.
 * User: UCT
 * Date: 2017/2/27
 * Time: 11:09
 */
namespace app\common\model;
use think\Model;

class Group extends Model{


    //显示所有数据
    function lists(){
        $ret = $this->select();
        return $ret;
    }

    //获取某个数据
    function detail($id){
        $ret = $this->where('group_id',$id)->find();
        return $ret;
    }

    //删除某个数据
    function deletes($id){
        if (is_array($id)){
            $ids = join(',',$id);
            $ret = $this->where("group_id in ($ids)")->delete();
        }else{
            $ret = $this->where("group_id",$id)->delete();
        }
        return $ret;
    }

}