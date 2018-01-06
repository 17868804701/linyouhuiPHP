<?php
// +----------------------------------------------------------------------
// | UCToo [ Universal Convergence Technology ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2017 http://uctoo.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Patrick <contact@uctoo.com>
// +----------------------------------------------------------------------
namespace app\common\behavior;
use think\Hook;
use think\Db;

// 初始化钩子信息
class InitHook {

    // 行为扩展的执行入口必须是run
    public function run(&$content){

        if(defined('BIND_MODULE') && BIND_MODULE === 'install') return;
        trace('初始化行为 in InitHook','info');
        $data = cache('hooks');
        trace(!$data,'info');
        trace(empty($data),'info');
        trace($data,'info');

        if(!$data){
            $hooks = Db::name('Hooks')->field('name,addons')->select();
            trace('data hooks in InitHook','info');
            trace($hooks,'debug');
         //   $hooksArray = $hooks->toArray();
         //   trace('data hooksArray in InitHook','info');
         //   trace($hooksArray,'debug');
            foreach ($hooks as $key => $value) {
                if($value['addons']){
                    trace($value,'info');
                    $map['status']  =   1;
                    $names          =   explode(',',$value['addons']);
                    $map['name']    =   array('IN',$names);
                    $data = Db::name('Addons')->field('id,name')->where($map)->select();
                    trace('data data in InitHook','info');
                    trace($data,'info');
                  //  $dataArray = $data->toArray();
                  //  trace('data dataArray in InitHook','info');
                  //  trace($dataArray,'debug');
                    if($data){
                        trace('data names in InitHook','info');
                        trace($names,'info');
                        trace('data  in InitHook','info');
                        trace($data,'info');
                        $dataCol = array_column($data, 'name','id');
                        trace('data dataCol in InitHook','info');
                        trace($dataCol,'info');

                        $addons = array_intersect($names, $dataCol);

                        trace('data addons in InitHook','info');
                        trace($addons,'info');

                        Hook::add($value['name'],array_filter(array_map('get_addon_class',$addons)));
                    }
                }
            }
            cache('hooks',Hook::get());    //这里缓存出错了先注释掉，TODO:启用缓存
        }else{
            Hook::import($data,false);
        }
        trace('end in InitHook','info');
        trace(Hook::get(),'info');
    }
}