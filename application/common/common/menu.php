<?php
/**
 * Created by PhpStorm.
 * User: UCT
 * Date: 2017/2/10
 * Time: 15:24
 */

function getMenuList()
{
    //根据角色权限过滤菜单
    $menu_list = getAllMenu();
    $role_right = '';
    $right = db('menu')->where("is_del",0)->cache(true)->column('right');
    foreach ($right as $val) {
        $role_right .= $val . ',';
    }
    $role_right = explode(',', $role_right);
    foreach ($menu_list as $k => $mrr) {
        foreach ($mrr['sub_menu'] as $j => $v) {
            if (!in_array($v['control'] . 'Controller@' . $v['act'], $role_right)) {
                    unset($menu_list[$k]['sub_menu'][$j]);//过滤菜单
                }
            }
        }

    return $menu_list;
}

function getAllMenu()
{
    return array(
        'system' => array('name' => '系统设置', 'icon' => 'fa-cog', 'sub_menu' => array(
            array('name' => '网站设置', 'act' => 'index', 'control' => 'System'),
            array('name' => '友情链接', 'act' => 'linkList', 'control' => 'Article'),
            array('name' => '自定义导航', 'act' => 'navigationList', 'control' => 'System'),
            array('name' => '区域管理', 'act' => 'region', 'control' => 'Tools'),
            array('name' => '权限资源列表', 'act' => 'right_list', 'control' => 'System'),
        )),
        'access' => array('name' => '权限管理', 'icon' => 'fa-gears', 'sub_menu' => array(
            array('name' => '管理员列表', 'act' => 'index', 'control' => 'Admin'),
            array('name' => '角色管理', 'act' => 'role', 'control' => 'Admin'),
            array('name' => '供应商管理', 'act' => 'supplier', 'control' => 'Admin'),
            array('name' => '管理员日志', 'act' => 'log', 'control' => 'Admin'),
        )),
        'member' => array('name' => '会员管理', 'icon' => 'fa-user', 'sub_menu' => array(
            array('name' => '会员列表', 'act' => 'index', 'control' => 'User'),
            array('name' => '会员等级', 'act' => 'levelList', 'control' => 'User'),
            array('name' => '充值记录', 'act' => 'recharge', 'control' => 'User'),
            //array('name'=>'会员整合','act'=>'integrate','control'=>'User'),
        )),
        'goods' => array('name' => '商品管理', 'icon' => 'fa-book', 'sub_menu' => array(
            array('name' => '商品分类', 'act' => 'categoryList', 'control' => 'Goods'),
            array('name' => '商品列表', 'act' => 'goodsList', 'control' => 'Goods'),
            array('name' => '商品类型', 'act' => 'goodsTypeList', 'control' => 'Goods'),
            array('name' => '商品规格', 'act' => 'specList', 'control' => 'Goods'),
            array('name' => '商品属性', 'act' => 'goodsAttributeList', 'control' => 'Goods'),
            array('name' => '品牌列表', 'act' => 'brandList', 'control' => 'Goods'),
            array('name' => '商品评论', 'act' => 'index', 'control' => 'Comment'),
            array('name' => '商品咨询', 'act' => 'ask_list', 'control' => 'Comment'),
        )),
        'order' => array('name' => '订单管理', 'icon' => 'fa-money', 'sub_menu' => array(
            array('name' => '订单列表', 'act' => 'index', 'control' => 'Order'),
            array('name' => '发货单', 'act' => 'delivery_list', 'control' => 'Order'),
            //array('name' => '快递单', 'act'=>'express_list', 'control'=>'Order'),
            array('name' => '退货单', 'act' => 'return_list', 'control' => 'Order'),
            array('name' => '添加订单', 'act' => 'add_order', 'control' => 'Order'),
            array('name' => '订单日志', 'act' => 'order_log', 'control' => 'Order'),
        )),
        'promotion' => array('name' => '促销管理', 'icon' => 'fa-bell', 'sub_menu' => array(
            array('name' => '抢购管理', 'act' => 'flash_sale', 'control' => 'Promotion'),
            array('name' => '团购管理', 'act' => 'group_buy_list', 'control' => 'Promotion'),
            array('name' => '商品促销', 'act' => 'prom_goods_list', 'control' => 'Promotion'),
            array('name' => '订单促销', 'act' => 'prom_order_list', 'control' => 'Promotion'),
            array('name' => '代金券管理', 'act' => 'index', 'control' => 'Coupon'),
        )),
        'Ad' => array('name' => '广告管理', 'icon' => 'fa-flag', 'sub_menu' => array(
            array('name' => '广告列表', 'act' => 'adList', 'control' => 'Ad'),
            array('name' => '广告位置', 'act' => 'positionList', 'control' => 'Ad'),
        )),
        'content' => array('name' => '内容管理', 'icon' => 'fa-comments', 'sub_menu' => array(
            array('name' => '文章列表', 'act' => 'articleList', 'control' => 'Article'),
            array('name' => '文章分类', 'act' => 'categoryList', 'control' => 'Article'),
            //array('name' => '帮助管理', 'act'=>'help_list', 'control'=>'Article'),
            //array('name' => '公告管理', 'act'=>'notice_list', 'control'=>'Article'),
            array('name' => '专题列表', 'act' => 'topicList', 'control' => 'Topic'),
        )),
        'weixin' => array('name' => '微信管理', 'icon' => 'fa-weixin', 'sub_menu' => array(
            array('name' => '公众号管理', 'act' => 'index', 'control' => 'Wechat'),
            array('name' => '微信菜单管理', 'act' => 'menu', 'control' => 'Wechat'),
            array('name' => '文本回复', 'act' => 'text', 'control' => 'Wechat'),
            array('name' => '图文回复', 'act' => 'img', 'control' => 'Wechat'),
            // array('name' => '组合回复', 'act' => 'nes', 'control' => 'Wechat'),
            // array('name' => '抽奖活动', 'act'=>'nes', 'control'=>'Wechat'),
            // array('name' => '消息推送', 'act'=>'news', 'control'=>'Wechat'),
        )),
        'theme' => array('name' => '模板管理', 'icon' => 'fa-adjust', 'sub_menu' => array(
            array('name' => 'PC端模板', 'act' => 'templateList?t=pc', 'control' => 'Template'),
            array('name' => '手机端模板', 'act' => 'templateList?t=mobile', 'control' => 'Template'),
        )),

        'distribut' => array('name' => '分销管理', 'icon' => 'fa-cubes', 'sub_menu' => array(
//					array('name' => '分销商品列表', 'act'=>'goods_list', 'control'=>'Distribut'),
            array('name' => '分销商列表', 'act'=>'distributor_list', 'control'=>'Distribut'),
            array('name' => '分销关系', 'act' => 'tree', 'control' => 'Distribut'),
            array('name' => '分销设置', 'act' => 'set', 'control' => 'Distribut'),
            array('name' => '提现申请', 'act' => 'withdrawals', 'control' => 'Distribut'),
            array('name' => '分成日志', 'act' => 'rebate_log', 'control' => 'Distribut'),
            array('name' => '汇款记录', 'act' => 'remittance', 'control' => 'Distribut'),
        )),

        'tools' => array('name' => '插件工具', 'icon' => 'fa-plug', 'sub_menu' => array(
            array('name' => '插件列表', 'act' => 'index', 'control' => 'Plugin'),
            array('name' => '数据备份', 'act' => 'index', 'control' => 'Tools'),
            array('name' => '数据还原', 'act' => 'restore', 'control' => 'Tools'),
        )),
        'count' => array('name' => '统计报表', 'icon' => 'fa-signal', 'sub_menu' => array(
            array('name' => '销售概况', 'act' => 'index', 'control' => 'Report'),
            array('name' => '销售排行', 'act' => 'saleTop', 'control' => 'Report'),
            array('name' => '会员排行', 'act' => 'userTop', 'control' => 'Report'),
            array('name' => '销售明细', 'act' => 'saleList', 'control' => 'Report'),
            array('name' => '会员统计', 'act' => 'user', 'control' => 'Report'),
            array('name' => '财务统计', 'act' => 'finance', 'control' => 'Report'),
        )),
        'pickup' => array('name' => '自提点管理', 'icon' => 'fa-anchor', 'sub_menu' => array(
            array('name' => '自提点列表', 'act' => 'index', 'control' => 'Pickup'),
            array('name' => '添加自提点', 'act' => 'add', 'control' => 'Pickup'),
        )),
        'oneshop' => array('name' => '夺宝管理', 'icon' => 'fa-anchor', 'sub_menu' => array(
            array('name' => '区域列表', 'act' => 'dbtypelist', 'control' => 'OneShop'),
            array('name' => '夺宝列表', 'act' => 'shoplist', 'control' => 'OneShop'),
            array('name' => '夺宝订单列表', 'act' => 'db_order', 'control' => 'OneShop'),
            array('name' => '评论列表', 'act' => 'comment_list', 'control' => 'OneShop'),
        )),
        'yellow' => array('name' => '黄页管理', 'icon' => 'fa-anchor', 'sub_menu' => array(
            array('name' => '商家类型', 'act' => 'yellow_type', 'control' => 'Yellow'),
            array('name' => '商家管理', 'act' => 'yellow_info_list', 'control' => 'Yellow'),
            array('name' => '广告管理', 'act' => 'ad_yellow_list', 'control' => 'Yellow'),
        )),
        'video' => array('name' => '视频管理', 'icon' => 'fa-anchor', 'sub_menu' => array(
            array('name' => '视频类型', 'act' => 'video_type_list', 'control' => 'Video'),
            array('name' => '视频管理', 'act' => 'video_list', 'control' => 'Video'),
//            array('name' => '评论管理', 'act' => 'ad_video_list', 'control' => 'Video'),
        ))
    );
}


function respose($res)
{
    exit(json_encode($res));
}

function getAdminInfo($admin_id)
{
    return db('ucenter_member')->where("id",$admin_id)->find();
}

/**
 * 面包屑导航  用于后台管理
 * 根据当前的控制器名称 和 action 方法
 */
function navigate_admin()
{
    $navigate = include APP_PATH . 'Common/Conf/navigate.php';
    $location = strtolower('Admin/' . request()->controller());
    trace($location,'控制器名');
    $arr = array(
        '后台首页' => 'javascript:void();',
        $navigate[$location]['name'] => 'javascript:void();',
        $navigate[$location]['action'][request()->action()] => 'javascript:void();',
    );
    return $arr;
}
