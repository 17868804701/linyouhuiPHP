<?php
/**
 * Created by PhpStorm.
 * User: UCT
 * Date: 2017/2/28
 * Time: 10:06
 */
namespace app\common\model;
use think\Model;

class ShopSlides extends Model{

    function getLists(){
        $slides = $this->where("status",0)->order("id desc")->limit(3)->select();
        foreach ($slides as &$s) {
            $s['image'] = get_cover($s['image'], 'url');
        }
        return $slides;
    }
}