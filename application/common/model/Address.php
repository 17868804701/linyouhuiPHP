<?php
namespace app\common\model;

use think\Model;

class Address extends Model{
//    protected $table = 'uctoo_shop_user_address';
    protected $autoWriteTimestamp = true;
    protected $createTime = false;
    protected $updateTime = 'modify_time';

}