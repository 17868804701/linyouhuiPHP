<?php
namespace app\api\controller;
use think\Controller;
use think\Request;
use app\shop\model\Shop;
use app\common\model\Message as Msg;
class Message extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $id = input('id');
        $status = input('status',0);
        $read = input('read');
        if (isset($read)){
            $msg = db('ShopMessages')->where(array("owner_id"=>$id,'type'=>$status,'status'=>$read))->order('id','desc')->select();
            foreach ($msg as &$m){
                $m['time'] = date('Y-m-d',$m['create_time']);
            }
            return json($msg);
        }
        if (empty($id))
            return false;
        //查取相关信息    "owner_id"  'status'=>0,
        $msg = db('ShopMessages')->where(array("owner_id"=>$id,'type'=>$status))->order('id','desc')->select();
        foreach ($msg as &$m){
            $m['time'] = date('Y-m-d',$m['create_time']);
        }
        return json($msg);
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
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        trace(input('param.'),'消息提交');
        $data = array();
        $data['owner_id'] = input('user_id'); //缓过来
        $data['shop_id'] = input('shop_id');
        $data['user_id'] = input('owner_id');
        $id = input('id');
        $type = input('types');
        $data['goods_id'] = input('goods_id');
        $data['goods_name'] = input('goods_name');            //通过商品
        $data['create_time'] = time();
        $data['brief'] = $data['goods_name'].'已通过上架审核';
        $data['type'] = 3;//普通小学
        db('ShopGoods')->where('id',$data['goods_id'])->setField(['is_on_sale'=>0]);
        db('ShopMessages')->where('id',$id)->setField(['status'=>1]);
        $ret = db('ShopMessages')->insert($data);
        return $ret;
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
        $ret = db('ShopMessages')->where('id',$id)->setField(['status'=>1]);
        return $ret;
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
        //
    }
    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $data = array();
        $data['owner_id'] = input('user_id'); //缓过来
        $data['shop_id'] = input('shop_id');
        $data['user_id'] = input('owner_id');
        $id = input('id');
        $type = input('types');
        $data['goods_id'] = input('goods_id');
        $data['goods_name'] = input('goods_name');
        $data['create_time'] = time();
        $data['brief'] = $data['goods_name'].'上架审核被驳回';
        $data['type'] = 3;//普通小学
        db('ShopMessages')->where('id',$id)->delete();
        $ret = db('ShopMessages')->insert($data);
        return $ret;
    }
}