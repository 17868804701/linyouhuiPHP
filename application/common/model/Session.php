<?php

namespace app\common\model;

use think\Model;

class Session extends Model
{
    //
    protected $autoWriteTimestamp = true;
//    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
//    protected $pk = 'session_id';

    protected static function init()
    {
      //  Session::beforeUpdate(function ($session) {
      //      if ($session->update_time <= time()-*3024*60*60) {   //超过session有效期自动删除
      //          $session->delete();
      //          return true;
      //      }
      //  });
    }


}
