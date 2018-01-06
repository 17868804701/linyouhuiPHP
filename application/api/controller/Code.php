<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\shop\model\Shop;
use think\File;
use app\common\model\ShopOrder;
use app\common\model\OrderGoods;

class Code extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $id = input('id');//订单
        $order = db('OrderGoods')->find($id);
//        dump($order);
//        $order['goods'] = array();
//        $goods = array();
//        foreach ($order as &$val){
//            $goods = db('OrderGoods')->where("order_id",$id)->select();
//        }
//        $order['goods'] = $goods;
        $this->assign('order',$order);
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {

    }

    /**
     * 用来提交表单数据保存到数据库
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $id = input('id');
        trace($id,'确认收货');

        $item = OrderGoods::where('id',$id)->find();
        OrderGoods::where('id',$id)->setField(['is_send'=>2,'rec_time'=>date('Y-m-d H:i:s',time())]);

        $data = array();
        $data['user_id'] = $item['user_id']; //缓过来
        $data['user_name'] = $item['user_name']; //缓过来
        $data['owner_id'] = $item['owner_id'];
        $data['goods_id'] = $item['goods_id'];
        $data['goods_name'] = $item['goods_name'];
        $data['create_time'] = time();
        $data['brief'] = $item['user_name'].'-买家确认收货';
        $data['type'] = 3;//上架
        $ret = db('ShopMessages')->insert($data);
        return $ret;
//        $url = input('url');
////        $url = "https://www.pangxx.cn/order/id".$id;
//        $img = getUrlQRCode($url);
//        $name = 'public\uploads\picture\\'.time().rand(0,10).'.png';
////        file_put_contents($name,getUrlQRCode($url));
//        $file = fopen(ROOT_PATH.$name,"w");//打开文件准备写入
//        fwrite($file,getUrlQRCode($url));//写入
//        fclose($file);//关闭
        return getUrlQRCode($url);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $param = input('param.');
        trace($param,'提交了什么');
        $param['goods_id'] = $id;
        foreach( $param as $k=>$v) {           //整理参数
            if('' == $v) unset($param[$k]);
        }
        $ret = model('shop_goods')->edit($param);

        if ($ret){
            return  json(array('status'=>1,'msg'=>'添加成功'));
        }else{
            return  json(array('status'=>0,'msg'=>'添加失败'));
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $ret = model('shop_goods')->where(['id'=>$id])->delete();

        if ($ret){
            return  json(array('status'=>1,'msg'=>'删除成功'));
        }else{
            return  json(array('status'=>0,'msg'=>'删除失败'));
        }
    }
}
