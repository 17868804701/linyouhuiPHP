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

namespace app\mpbase\controller;

use app\admin\controller\Admin;
use com\TPWechat;
use app\admin\builder\AdminConfigBuilder;
use app\common\model\CustomMenu as Cusmenu;
/**
 * 微信自定义菜单配置控制器
 * @author patrick <contact@uctoo.com>
 */
class Custommenu extends Admin
{
    protected $weObj;          //管理后台自动注入的wechat SDK实例,用于管理公众号，自定义微信会员卡、优惠券、运营人员与微会员互动等场景

    //TP5 的架构方法绑定（属性注入）的对象
    public function __construct(TPWechat $weObj)
    {
        $this->weObj = $weObj;
        parent::__construct();
    }

    /**
     * 菜单列表
     * @author patrick <contact@uctoo.com>
     */
    public function index($getwechatmenu = null)
    {

        $cm = model('CustomMenu');
        if (request()->isPost()) {
            $one = $_POST['cm'][1];

            if (count($one) > 0) {
                $map['token'] = get_token();
                $cm->where($map)->delete();

                for ($i = 0; $i < count(reset($one)); $i++) {
                    $data[$i] = array(
                        'pid' => 0,
                        'sort' => intval($one['sort'][$i]),
                        'title' => op_t($one['title'][$i]),
                        'keyword' => op_t($one['keyword'][$i]),
                        'url' => op_t($one['url'][$i]),
                        'token' => op_t($one['token'][$i]),
                        'type' => op_t($one['type'][$i]),
                        'status' => 1
                    );
                    $pid[$i] = $cm->save($data[$i]);
                }
                $two = $_POST['cm'][2];

                for ($j = 0; $j < count(reset($two)); $j++) {
                    $data_two[$j] = array(
                        'pid' => $pid[$two['pid'][$j]],
                        'sort' => intval($two['sort'][$j]),
                        'title' => op_t($two['title'][$j]),
                        'keyword' => op_t($two['keyword'][$j]),
                        'url' => op_t($two['url'][$j]),
                        'token' => op_t($two['token'][$j]),
                        'type' => op_t($two['type'][$j]),
                        'status' => 1
                    );
                    $res[$j] = $cm->save($data_two[$j]);
                }
                $this->success('修改成功');
            }
            $this->error('菜单至少存在一个。');
        } else {
            /* 获取菜单列表 */
            $map = array('status' => array('gt', -1), 'token' => get_token(), 'pid' => 0);
            $list = $cm->where($map)->order('sort asc,id asc')->select();
//            trace('获取菜单列表', 'info');
//            trace($list, 'info');
//            foreach ($list as $k => &$v) {
//                $child = $cm->where(array('status' => array('gt', -1), 'pid' => $v['id']))->order('sort asc,id asc')->select();
//                foreach ($child as $key => &$val) {
//
//                }
//                unset($key, $val);
//                $child && $v['child'] = $child;
//                trace('获取菜单列表child', 'info');
//                trace($child, 'info');
//            }
//
//            unset($k, $v);
//            $this->assign('type', $cm->getCmType());
//            trace('获取菜单列表Type', 'info');
//            trace($cm->getCmType(), 'info');
//            trace('获取菜单列表getwechatmenu', 'info');
//            $getwechatmenu = input('getwechatmenu');
//            trace($getwechatmenu, 'info');
//            if ($getwechatmenu) {
//                $this->assign('list', $getwechatmenu);
//                //   $this->assign('cm', $getwechatmenu);
//                trace('获取菜单列表list1', 'info');
//                trace($getwechatmenu, 'info');
//            } else {
//                $this->assign('list', $list);
//                //   $this->assign('cm', $list);
//                trace('获取菜单列表list2', 'info');
//                trace($list, 'info');
//            }

            $this->assign('list', $list);
            $this->meta_title = '自定义菜单管理';
            return $this->fetch('index');
        }

    }

    /*
     * 获取自定义菜单
     * */
    public function getmenu()
    {

        $menu = $this->weObj->getMenu();
        $model = new Cusmenu();

        $data = array();
        trace('获取菜单列表menu', 'info');
        trace($menu, 'info');
        if (!$menu) {
            $this->error('请确认公众号权限');
        }else{
            //删除本地数据更新
            $map['token'] = get_token();
            $model::destroy($map);
        }
        foreach ($menu['menu']['button'] as $k => &$v) {

            $v['title'] = $v['name'];
            $model->title = $v['name'];
            $model->pid = 0;
            if (array_key_exists('key', $v)) {
                $v['keyword'] = $v['key'];
                $model->keyword = $v['key'];
            } else {
                $v['keyword'] = null;
                $model->keyword = '';
            }

            if (array_key_exists('url', $v)) {
                $model->url = $v['url'];
            } else {
                $v['url'] = null;
                $model->url = '';
            }
            if (array_key_exists('type', $v)) {
//                !$v['type'] ? $v['type'] = 'none' : null;
                $model->type = $v['type'];
            } else {
                $v['type'] = null;
                $model->type = '';
            }
            $model->id = null;
            $model->token = get_token();
            $model->isUpdate(false)->save();
            $pid = $model->id;
            foreach ($v['sub_button'] as &$v1) {
//                dump($v);
                $model->title = $v1['name'];
                $model->pid = $pid;
                if (array_key_exists('key', $v1)) {
                    $v['keyword'] = $v1['key'];
                    $model->keyword = $v1['key'];
                } else {
                    $v['keyword'] = null;
                    $model->keyword = '';
                }

                if (array_key_exists('url', $v1)) {
                    $model->url = $v1['url'];
                } else {
                    $v['url'] = null;
                    $model->url = '';
                }
                if (array_key_exists('type', $v1)) {
                    $model->type = $v1['type'];
                }else {
                    $v1['type'] = null;
                    $model->type = '';
                }
                $model->id = null;
                $model->token = get_token();
                $model->isUpdate(false)->save();

            }
            $v['child'] = $v['sub_button'];
        }
        $map = array('status' => array('gt', -1), 'token' => get_token(), 'pid' => 0);
        $list = $model->where($map)->order('sort asc,id asc')->select();
        trace('获取菜单列表fetch', 'info');
        trace($menu, 'info');
//        dump($menu['menu']);
//        $this->assign('list',$menu['menu']['button']);
        $this->assign('list',$list);
        return $this->fetch('index');
//        return $this->fetch('Custommenu/index', ['getwechatmenu' => $menu['menu']['button']]);
    }

    /*
     * 菜单预览
     */
    public function previewmenu()
    {
        $menu = input('post.');
        $menu = $menu['cm'];
//         dump($menu);
        foreach ($menu['1']['title'] as $v) {
            $preview[] = array('name' => $v);
        };

        foreach ($menu['2']['pid'] as $k => $v) {
            $preview[$v]['child'][] = array('sort' => $menu['2']['sort'][$k], 'title' => $menu['2']['title'][$k]);
        }
        //dump($preview);
        $this->assign('menu', $preview);
        return $this->fetch();

    }


    /**
     * 自定义菜单列表
     * @author patrick <contact@uctoo.com>
     */
    public function index1()
    {
        $pid = input('get.pid', 0);
        /* 获取菜单列表 */
        $map = array('status' => array('gt', -1), 'pid' => $pid);
        $list = D('Mpbase/CustomMenu')->where($map)->order('sort asc,id asc')->select();

        $this->assign('list', $list);
        $this->assign('pid', $pid);
        $this->meta_title = '自定义菜单管理';
        $this->display();
    }


    /**
     * 增加菜单
     * @author patrick <contact@uctoo.com>
     */
    public function add()
    {

        $id = input('id', 0);
        if (request()->isPost()) {
            $cm = db('CustomMenu');
            $data = input('post.');
            if (isset($data['id'])) {
                $id = $cm->where('id',$data['id'])->update($data);

            } else {
                $id = $cm->insert($data);
            }

            if ($id) {
                $this->success('新增成功', url('index'));

            } else {
                $this->error('新增失败');
            }
        } else {
            // 要先填写appid
            $map ['public_id'] = get_token();
            $info = db('MemberPublic')->where($map)->find();

            if (empty ($info ['appid']) || empty ($info ['secret'])) {
                $this->error('请先配置appid和secret', url('mpbase/Mpbase/index', 'id=' . $info ['id']));
            }
            //获取父导航
//            if (!empty($pid)) {
//                $parent = db('CustomMenu')->where(array('id' => $pid))->field('title')->find();
//                $this->assign('parent', $parent);
//
//            }
            $pcm = db('CustomMenu')->where(array('pid' => 0))->column('id,title');
            $pcm[0] ='顶级菜单';

            $types = model('CustomMenu')->getCmType();
            $array = array('click' => '点击推事件', 'view' => '跳转URL', 'scancode_push' => '扫码推事件', 'scancode_waitmsg' => '扫码带提示', 'pic_sysphoto' => '弹出系统拍照发图', 'pic_photo_or_album' => '弹出拍照或者相册发图', 'pic_weixin' => '弹出微信相册发图器', 'location_select' => '弹出地理位置选择器', 'none' => '无事件的一级菜单');

            if ($id != 0){
                $data = db('CustomMenu')->find($id);
            }else{
                $data = array();
            }

//            $this->assign('pcm', $pcm);
//            $this->assign('types', $types);
//            $this->assign('pid', $pid);
//            $this->assign('info', null);
//            $this->meta_title = '新增菜单';

            $builder = new AdminConfigBuilder();
            $builder->title('编辑菜单')
                ->keyId()
                ->keyText('sort','优先级','菜单显示顺序')
                ->keyText('title','标题','菜单显示')
                ->keySelectArr('type','类型','必选',$types)
                ->keyText('keyword','关键字','关联关键字')
                ->keyText('url','链接','跳转带http://的URL')
                ->keySelect('pid','父菜单','仅支持二级',$pcm)
                ->data($data)
                ->buttonSubmit(url('add'))
                ->buttonBack();
            return $builder->fetch();


//            return $this->fetch('edit');
        }
    }

    /**
     * 编辑自定义菜单
     * @author patrick <contact@uctoo.com>
     */
    public function edit($id = 0)
    {
        if (request()->isPost()) {
            $cm = db('CustomMenu');
            $data = input('post.');
            if ($data) {
                if ($cm->insert($data)) {

                    $this->success('编辑成功', url('index'));
                } else {
                    $this->error('编辑失败');
                }

            } else {
                $this->error($cm->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = db('CustomMenu')->find($id);

            if (false === $info) {
                $this->error('获取配置信息错误');
            }

            $pid = input('get.pid', 0);

            //获取父菜单
            if (!empty($pid)) {
                $parent = db('CustomMenu')->where(array('id' => $pid))->column('title');
                $this->assign('parent', $parent);
            }
            $pcm = db('CustomMenu')->where(array('pid' => 0))->select();
            $this->assign('pcm', $pcm);
            $this->assign('pid', $pid);
            $this->assign('info', $info);
            $this->meta_title = '编辑自定义菜单';
            return $this->fetch();
        }
    }

    /**
     * 删除自定义菜单
     * @author patrick <contact@uctoo.com>
     */
    public function del()
    {
        $id = array_unique((array)input('id', 0));

        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id));
        if (db('Mpbase/CustomMenu')->where($map)->delete()) {

            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 向微信平台提交生成自定义菜单
     * @author patrick <contact@uctoo.com>
     */
    public function create()
    {
        $data = $this->get_data();
        trace($this->weObj->json_encode($data),'菜单2222');

        // 要先填写appid
        $map ['public_id'] = get_token();
        $info = db('MemberPublic')->where($map)->find();

        if (empty ($info ['appid']) || empty ($info ['secret'])) {
            $this->error('请先配置公众号的appid和secret', url('Admin/Mpbase/index', 'id=' . $info ['id']));
        }

        $res = $this->weObj->createMenu($data);

        if ($res) {
            $this->success('发送菜单成功', url('Custommenu/index'), 3);
        } else {
            $this->success('发送菜单失败，错误的返回码是：' . $this->weObj->errCode . ', 错误的提示是：' . $this->weObj->errMsg, url('Custommenu/index'), 5);
        }
    }

    function get_data($map = array())
    {
        $map ['token'] = get_token();
        $list = db('custom_menu')->where($map)->order('pid asc, sort asc')->select();

        foreach ($list as $k => $d) {
            if ($d ['pid'] != 0)
                continue;
            $tree ['button'] [$d ['id']] = $this->_deal_data($d);
            unset ($list [$k]);
        }
        foreach ($list as $k => $d) {
            $tree ['button'] [$d ['pid']] ['sub_button'] [] = $this->_deal_data($d);
            unset ($list [$k]);
        }
        $tree2 = array();
        $tree2 ['button'] = array();

        foreach ($tree ['button'] as $k => $d) {
            $tree2 ['button'] [] = $d;
        }
        return $tree2;
    }

    function _deal_data($d)
    {
        $res ['name'] = $d ['title'];

        if ($d['type'] == 'view') {
            $res ['type'] = 'view';
            $res ['url'] = trim($d ['url']);
        } elseif ($d['type'] != 'none') {
            $res ['type'] = trim($d['type']);
            $res ['key'] = trim($d ['keyword']);
        } elseif ($d['type'] == 'none') {  //无事件的一级菜单
        }
        return $res;
    }

    /**
     * 导航排序
     * @author huajie <banhuajie@163.com>
     */
    public function sort()
    {
        if (request()->isGet()) {
            $ids = input('get.ids');
            $pid = input('get.pid');

            //获取排序的数据
            $map = array('status' => array('gt', -1));
            if (!empty($ids)) {
                $map['id'] = array('in', $ids);
            } else {
                if ($pid !== '') {
                    $map['pid'] = $pid;
                }
            }
            $list = db('CustomMenu')->where($map)->field('id,title')->order('sort asc,id asc')->select();

            $this->assign('list', $list);
            $this->meta_title = '自定义菜单排序';
            $this->display();
        } elseif (request()->isPost()) {
            $ids = input('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key => $value) {
                $res = db('CustomMenu')->where(array('id' => $value))->setField('sort', $key + 1);
            }
            if ($res !== false) {
                $this->success('排序成功！');
            } else {
                $this->eorror('排序失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
   