<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

Route::resource('orders','api/Order');
Route::resource('shop_product','api/ShopProduct');
Route::resource('shop_product_cats','api/ShopProductCats');
Route::resource('user','api/User');
Route::resource('code','api/Code');
Route::resource('goods','api/Goods');
Route::resource('mygoods','api/MyGoods');
Route::resource('allgoods','api/AllGoods');//店铺所有
Route::resource('order','api/Order');
Route::resource('buyorder','api/BuyOrder');
Route::resource('ordergoods','api/OrderGoods');
Route::resource('upload','api/Upload');
Route::resource('check','api/Check');//验证
Route::resource('spec','api/Spec');//规格
Route::resource('delivery','api/Delivery');//快递模板
Route::resource('message','api/Message');//审核消息
Route::resource('group','api/Group');//群组店铺
Route::resource('comment','api/Comment');//评论
Route::resource('address','api/Address');
Route::resource('slides','api/Slides');
Route::resource('goodsapi','api/GoodsApi');