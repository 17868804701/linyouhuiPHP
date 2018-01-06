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
/**
 *	公众号会员管理后台控制器
 *  @version 1.0
 */
namespace app\ucuser\controller;

use app\admin\controller\Admin;
use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminListBuilder;
use app\admin\builder\AdminTreeListBuilder;
use app\common\model\Ucuser as UcuserModel;
use think\Db;
use com\TPWechat;
use com\ErrCode;
use app\common\model\Verify;


class Ucuser extends Admin
{

    protected $weObj;          //管理后台自动注入的wechat SDK实例,用于管理公众号，自定义微信会员卡、优惠券、运营人员与微会员互动等场景

    //TP5 的架构方法绑定（属性注入）的对象
    public function __construct(TPWechat $weObj)
    {
        $this->weObj = $weObj;
        parent::__construct();
    }

    public function index($page=1,$r=10)
    {
        $model = model('Ucuser');
		$nickname = input('nickname');
        if(!empty($nickname)){
            $map = array('nickname'=>$nickname);
        }else{
            $map = '';
        }

		$list = db('Ucuser')->where($map)->order('mid desc')->select();
        $totalCount = $model->count();
        //显示页面
        $builder = new AdminListBuilder();
		$builder
			->setIdsKey('mid')
			->title('用户列表')
            ->setSearchPostUrl(url())
            ->search('', 'nickname', 'text', '昵称', '', '', '')
            ->ajaxButton(url('ucuser/delete'),array(),'删除')
			->select('查看模式：', 'show_type', 'select', '', '', '', array(array('id'=>'','value'=>'用户资料'),array('id'=>1,'value'=>'用户标签')))
			->keyText('mid', 'mid')
			->keyText('nickname', '昵称')
            ->keyImage('headimgurl','头像',array('style'=>'width:90px;hight:90px;border-radius:10px;'))
            ->keyMap('sex', '性别',array(0 => '未知', 1 => '<span class="btn btn-sm" ><i class="icon icon-venus" style="color:blue"></i>男性</span>', 2 => '<span class="btn btn-sm " ><i class="icon icon-mars" style="color:orange"></i>女性</span>'))
            ->keyText('mobile','手机')
            ->keyText('wechat','微信')
//            ->keyText('country','国家')
            ->keyText('province','省份')
            ->keyText('city','城市')
            ->keyMap('status','状态',array(0=>'正常',1=>'禁用'));


		return $builder
			->data($list)
			->pagination($totalCount, $r)
			->fetch();
    }

    //群主/管理员
    public function admin($page=1,$r=10)
    {
        //$list = db('ucuser')->where('groupid <> 0')->order('mid desc')->select();
        $db_prefix=config('table_prefix');
        $list = Db::table("{$db_prefix}ucuser")->alias('u')->join("{$db_prefix}shop s",'u.mid = s.shop_owner_id')->select();
//        dump($list);
        $totalCount = count($list);
        //显示页面
        $builder = new AdminListBuilder();
        $builder
            ->setIdsKey('mid')
            ->title('用户列表')
            ->ajaxButton(url('ucuser/delete'),array(),'删除')
            ->select('查看模式：', 'show_type', 'select', '', '', '', array(array('id'=>'','value'=>'用户资料'),array('id'=>1,'value'=>'用户标签')))
            ->keyText('mid', 'mid')
            ->keyText('nickname', '昵称')
            ->keyImage('headimgurl','头像',array('style'=>'width:90px;hight:90px;border-radius:10px;'))
            ->keyMap('sex', '性别',array(0 => '未知', 1 => '<span class="btn btn-sm" ><i class="icon icon-venus" style="color:blue"></i>男性</span>', 2 => '<span class="btn btn-sm " ><i class="icon icon-mars" style="color:orange"></i>女性</span>'))
            ->keyText('shop_name','群名称')
            ->keyText('shop_member','群人数')
            ->keyText('city','城市')
            ->keyText('city','城市');
//            ->keyMap('status','状态',array(0=>'正常',1=>'禁用'));


        return $builder
            ->data($list)
            ->pagination($totalCount, $r)
            ->fetch();
    }

    //删除数据
    public function delete()
    {
				$ids = input('ids/a');
				trace($ids,'idd');
				if (!is_array($ids)){
                    $ids = array($ids);
                }
				$ret = model('ucuser')->destroy($ids);
				if ($ret)
                {

                    return ['status'=>true,'info'=>'操作成功。', url('shop/product')];
                }
                else
                {
                    return ['status'=>true,'info'=>'操作失败。'];
                }

    }

    /*
     *	编辑用户的标签
     */
	public function editUcuserTagLink()
	{
		$ids = input('ids/a','','intval');
		if(request()->isPost())
		{
			$taglink = input('taglink/a','','intval');
			is_numeric($ids) && $ids = array($ids);
			if(count($ids)==0)
			{
				$this->error('缺少mid');
			}
			$Ucuser = model('Ucuser');
			$UcTag = model("Ucuser/UcuserTag");
			if(count($taglink)>50)
			{
				$this->error('批量修改个数超过上限');
			}
			foreach($taglink as $tagid)
			{
				$Ucuser->startTrans();
				foreach ($ids as  &$mid )
				{
					$map = array('mid'=>$mid,'mp_id'=>get_mpid());
					$tagidlist = $Ucuser->where($map)->getfield('tagid_list');
					$tagidlist = empty($tagidlist)?array():json_decode($tagidlist,true);
					$tagidlist = array_merge($tagidlist,array($tagid));
					$tagidlist = array_unique($tagidlist);
					$tagidlist = array_values($tagidlist);
					if(count($tagidlist)>3)
					{
						$Ucuser->rollback();
						$this->error('修改失败，有用户超过3个标签');
					}
					empty($tagidlist) && $tagidlist =array();
					$ret= $Ucuser->where($map)->save(array('tagid_list'=>json_encode($tagidlist)));
					$ret && $UcTag->where(array('id'=>$tagid,'mp_id'=>get_mpid()))->setInconfig('count');
				}
				//todo 调用微信接口修改标签 失败则回滚
				$this->init_wx();
				$openid_list = $Ucuser->where(array('mid'=>array('in',$ids),'mp_id'=>get_mpid()))->getfield('openid',true);
				$ret = $this->weObj->batchtaggingTagsMembers($tagid,array($openid_list));
				if(!$ret)
				{
					$Ucuser->rollback();
					$this->error('批量打标签失败，错误：'.(ErrCode::getErrText($this->weObj->errCode)?ErrCode::getErrText($this->weObj->errCode):$this->weObj->errMsg));
				}
				$Ucuser->commit();
			}
			$this->success('修改成功');
		}
		else
		{
			if(	is_numeric($ids)
				|| (is_array($ids) && (count($ids)==1) && ($ids = $ids['0'])))
			{
				$map['mp_id'] = get_mpid();
				$map['mid'] = $ids;
				$Ucuser = model('Ucuser');
				$user_tagidlist = $Ucuser->where($map)->getField('tagid_list');
				$this->assign('user_tagidlist',json_decode($user_tagidlist,true));
			}
			$option['mp_id'] = get_mpid();
			$UcTag = model("Ucuser/UcuserTag");
			$ret = $UcTag->get_tag_list($option);
			$tagidlist = $ret['list'];
			foreach($tagidlist as $a)
			{
				if(in_array($a['id'],json_decode($user_tagidlist,true))|| ($a['id'] == 1)) continue;
				$inputs['checkbox'][] =array('key'=>'taglink','id'=>$a['id'],'type'=>'checkbox','name'=>$a['name'],'value'=>'false');
			}
			$this->assign('confirm_text','给用户打标签');
			$this->assign('inputs',$inputs);
			$this->assign('data',array('ids'=>$ids,''));
			$this->assign('posturl',U('ucuser/Ucuser/editUcuserTagLink'));
			$this->display('Public/popup');
		}
	}

	/*
	 *	删除某个用户的某个标签
	 */
	public function deleteTagLnik()
	{
		$mid = input('ids/a','','intval');
		is_numeric($mid) && $mid = array($mid);
		$id = input('id','','intval');
		is_numeric($id) && $id = array($id);
		isset($_REQUEST['taglink']) && $id = input('taglink/a','','intval');
		if(request()->isPost())
		{
			if(count($id)==0)
			{
				$this->error('请选择一个想要被撤销的标签');
			}
			if(count($id)>50)
			{
				$this->error('批量修改个数超过上限');
			}
			$Ucuser = model('Ucuser');
			$UcTag = model("Ucuser/UcuserTag");
			$map['mp_id'] = get_mpid();
			$map['mid'] = array('in',$mid);
			foreach($id as $tagid)
			{
				$map['tagid_list'] = $Ucuser->get_tag_id_map($tagid);
				$ucuser_info_list = $Ucuser->where($map)->Field('mid,openid,tagid_list')->select();
				if(empty($ucuser_info_list)) continue;
				$Ucuser->startTrans();
				foreach($ucuser_info_list as $ucuser_info)
				{
					$ucuser_info['tagid_list'] = json_decode($ucuser_info['tagid_list'],true);
					$tagid_list = array_diff($ucuser_info['tagid_list'],array($tagid));
					$tagid_list = array_values($tagid_list);
					$ret = $Ucuser->where(array('mid = '.$ucuser_info['mid']))
						->save(array('tagid_list'=>json_encode($tagid_list)));
					$ret && $UcTag->where(array('id'=>$tagid,'mp_id'=>get_mpid()))->setDeconfig('count');
				}
				//todo 调用微信接口删除标签 失败则回滚
				$this->init_wx();
				$openid = array_column($ucuser_info_list,'openid');
				$ret = $this->weObj->batchuntaggingTagsMembers($tagid,$openid);
				if(!$ret)
				{
					$Ucuser->rollback();
					$this->error('取消标签失败，错误：'.(ErrCode::getErrText($this->weObj->errCode)?ErrCode::getErrText($this->weObj->errCode):$this->weObj->errMsg));
				}
				$Ucuser->commit();
			}

			$this->success('成功取消标签');
		}
		else{

			if(	is_numeric($mid)
				|| (is_array($mid) && (count($mid)==1) && ($mid = $mid['0']) && isset($_REQUEST['id']))
				)
			{
				$this->assign('confirm_text','确定取消标签？');
				$this->assign('data',array('ids'=>$mid,'id'=>$id));
				$this->assign('posturl',url('ucuser/Ucuser/deleteTagLnik'));
				$this->display('Public/popup');
			}
			else{
				$option['mp_id'] = get_mpid();
				$UcTag = model("Ucuser/UcuserTag");
				$ret = $UcTag->get_tag_list($option);
				$tagidlist = $ret['list'];
				foreach($tagidlist as $a)
				{
					if($a['id'] == 1) continue;
					$inputs['checkbox'][] =array('key'=>'taglink','id'=>$a['id'],'type'=>'checkbox','name'=>$a['name'],'value'=>false);
				}
				$this->assign('confirm_text','确定要撤销用户标签？');
				$this->assign('inputs',$inputs);
				$this->assign('data',array('ids'=>$mid));
				$this->assign('posturl',url('ucuser/Ucuser/deleteTagLnik'));
				$this->display('Public/popup');
			}

		}
	}

	private function func_get_ucuser_tag(&$user_info)
	{
		if(!empty($user_info['tagid_list']))
		{
			$UcTag = db("UcuserTag");
			$user_info['tagid_list'] = json_decode($user_info['tagid_list'],true);
			asort($user_info['tagid_list']);
			$user_info['tagid_lists'] = '';

			for($i = 0;$i<	4;$i++)
			{
				if(!empty($user_info['tagid_list'][$i]))
				{
//					$user_info['tagid'.$i] = $user_info['tagid_list'][$i];
//					$user_info['tagid'.$i.'name'] = $UcTag->where(array('id'=>$user_info['tagid_list'][$i]))->getfield('name');

					$user_info['tagid_lists'] .= '<a class = "btn btn-sm btn-info" href="javascrapt:void(0);" modal-url="';
					$user_info['tagid_lists'] .= url('/ucuser/Ucuser/deleteTagLnik/',array('ids'=>$user_info['mid'],'id'=>$user_info['tagid_list'][$i])).'" data-role="modal_popup"> ';
					$user_info['tagid_lists'] .= $UcTag->where(array('id'=>$user_info['tagid_list'][$i]))->getfield('name').'<span class="text-danger">  X</span>';
					$user_info['tagid_lists'] .= '</a> ';
					if($user_info['tagid_list'][$i]==1)
					{
						$user_info['tagid_lists'] = '<sapn class="btn btn-danger btn-sm">黑名单</sapn>';
					}
				}
			}
		}
		return $user_info;
	}

	/*
	 * 将tag_id 装换为 select 格式
	 */
	private function get_tag_list_to_select()
	{
		$UcTag = db("UcuserTag");
		$option['mp_id'] = get_mpid();
		$ret = $UcTag->where($option)->field('id,name,count')->select();
		$select = array();
		if(empty($ret))
		{
			return false;
		}
		foreach($ret as $a)
		{
			if($a['id'] == 1) continue;
			$select[] = array('id'=>$a['id'],'value'=>$a['name'].'('.$a['count'].')');
		}
		return $select;
	}

    /**
     * 同步公众号粉丝数据
	 * 粉丝超过10w 未防止超时或超内存 跳转接力同步数据
     */
    public function sycUcuser(){

		($temp_id = input('temp_id',false,'/\w{13}/')) && $arr =  session($temp_id);
		$res = $this->weObj->getUserList((empty($arr['next_openid'])?'':$arr['next_openid']));
		if(!$res)
		{
			$this->error('获取微信粉丝列表错误，错误信息：'.ErrCode::getErrText($this->weObj->errCode));
		}
		$res['count'] = $res['count']+ (empty($arr['count'])?0:$arr['count']);


        $Ucuser = model("Ucuser"); // 实例化Ucuser对象
        $map['mp_id'] = get_mpid();
        $allUcuser = $Ucuser->where($map)->getField('mid,openid');
		empty($allUcuser) && $allUcuser = array();
        $diff = array_diff($res['data']['openid'],$allUcuser);

        foreach($diff as $i) {
          	$Ucuser->registerUser( get_mpid() ,$i);
        }
		//还有粉丝信息 跳转接力
		if($res['count'] < $res['total'])
		{
			empty($arr) && $temp_id =  uniqid();
			$arr = array('next_openid'=>$res['next_openid'],'count'=>$res['count']);
			session($temp_id,$arr);
			$this->success('正在获取粉丝列表('.$res['count'].'/'.$res['total'].')，请不要关闭页面',
				url('ucuser/Ucuser/sycucuser/temp_id/'.$temp_id));
		}
		else
		{
			$this->success('成功获取粉丝列表，数量：'.$res['total'].'个',url('index'));
		}
    }

    /**
     * 获取公众号粉丝信息
     */
    public function sycUcuserInfo(){

        $Ucuser = model("Ucuser"); // 实例化Ucuser对象
        $map['mp_id'] = get_mpid();
		$force = input('force','','intval');
		if($force)
		{
			$Ucuser->where($map)->update(array('status'=>1));
		}
		$mid = input('mid','','intval');
		empty($mid) || $map['mid'] = array('gt',$mid);
        $map['status'] = array('neq',2);//只取未同步的
        $allUcuser = $Ucuser->where($map)->limit(100)->field('openid,language as lang')->select();//微信接口一次最多允许拉取100个粉丝信息

		$openids = array('user_list'=>$allUcuser);
		$res = $this->weObj->getUsersInfo($openids);
		if(!$res)
		{
			$this->error('获取粉丝列表失败，错误：'.ErrCode::getErrText($this->weObj->errCode));
		}
		foreach($res['user_info_list'] as &$user_info)
		{
			$user_info['status'] = 2;
			$user_info['tagid_list'] = json_encode($user_info['tagid_list']);
			// 用户资料
			$Ucuser->where('openid = "'.addslashes($user_info['openid']).'"')->update($user_info);
			$last_openid = $user_info['openid'];
		}
		$mid = $Ucuser->where('openid = "'.addslashes($last_openid).'"')->column('mid');
		$map['mid'] = array('gt',$mid);
		$count = $Ucuser->where($map)->count();
		if($count>0)
		{
			$this->success('正在获取粉丝信息，剩余数量：'.$count.'，请不要关闭页面',
			url('ucuser/Ucuser/sycUcuserInfo/mid/'.$mid));
		}
		else
		{
			$this->success('同步粉丝数据成功',url('index'));
		}


    }

    /**
     * 删除公众号重复粉丝信息，忘记当时怎么写的算法了，好像效果是只保留openid相同的最大的那个mid记录
     * @param null $ids
     * @author patrick<contact@uctoo.com>
     */
    public function delDup($ids = null){

        $Ucuser = model("Ucuser"); // 实例化Ucuser对象
        $map['mp_id'] = get_mpid();
        $allUcuser = $Ucuser->where($map)->getField('mid,openid');

        $unDup = array_flip(array_flip($allUcuser));
        $dup = array_diff_assoc($allUcuser,$unDup);
        $dupKeys = array_keys($dup);
        $Ucuser->delete(arr2str($dupKeys));
        $this->success('数据剔重成功',U('index'));
    }

    public function config()
    {
        $list['url'] = url('Ucuser/Index/index', array('mp_id' => get_mpid()),true,true);

        //显示页面
        $builder = new AdminConfigBuilder();
        $builder
            ->title('微会员配置')
            ->keyTextArea('url', '微会员用户中心链接')
            ->data($list)
            ->display();
    }

    /**
     * 微会员统计
     * @author patrick<contact@uctoo.com>
     */
    public function stats()
    {
        //
        $builder = new AdminListBuilder();


    }


	public function ucuser_tag($page = 1,$r=10)
	{
		$UcTag = model("Ucuser/UcuserTag");
		$option['mp_id'] = get_mpid();
		$ret = $UcTag->get_tag_list($option);
		$totalCount = $ret['count'];
//		var_dump(__file__.' line:'.__line__,$ret);exit;
		$builder = new AdminListBuilder();
		$builder
			->title('用户列表')
			->button('同步微信标签列表', array('href' => U('/ucuser/Ucuser/sycUcuserTag')))
			->buttonModalPopup(U('/ucuser/Ucuser/createUcuserTags'),'','新建微信标签')
			->keyText('id', 'id')
			->keyLinkByFlag('name', '分组名','/ucuser/Ucuser/index/show_type/1&taglist%5B%5D=###&','id')
			->keyText('count', '数量')
			->keyDoActionModalPopup('/ucuser/Ucuser/updateUcuserTag/id/###', '重命名','操作')
			->keyDoActionModalPopup('/ucuser/Ucuser/deleteUcusersTag/id/###', '删除','操作')
			->data($ret['list'])
			->pagination($totalCount, $r)
			->display();
	}

	/*
	 * 删除用户标签组
	 */
	public function deleteUcusersTag()
	{
		$id = input('id','','int');
		if(request()->isPost())
		{
			$UcTag = model("Ucuser/UcuserTag");
			$Ucuser = model("Ucuser"); // 实例化Ucuser对象
			$map['mp_id'] = get_mpid();
			$map['id'] = $id;
			if(in_array($id,array('0','1','2')))
			{
				$this->error('这个标签默认保留的标签,不能修改');
			}
			$tagid = $UcTag->where($map)->find();
			if(empty($tagid))
			{
				$this->error('该公众号无此标签');
			}
			if($tagid['count']>100000)
			{
				$this->error('先进行取消标签的操作，直到粉丝数不超过10w后，才可直接删除该标签');
			}
			$map['tagid_list'] = $Ucuser->get_tag_id_map($id);
			$ucuser_list = $Ucuser->where($map)->field('mid,tagid_list')->select();
			$Ucuser->startTrans();
			foreach($ucuser_list as &$a)
			{

				$Ucuser->delete_tag_id($a,$id);
			}
			$UcTag->where($map)->delete();
			unset($ucuser_list);
			$this->init_wx();
			$ret = $this->weObj->deleteTags($id);
			if(!$ret)
			{
				$this->rollback();
				$this->error('删除公众号标签失败，错误：'.(ErrCode::getErrText($this->weObj->errCode)?ErrCode::getErrText($this->weObj->errCode):$this->weObj->errMsg));
			}
			$UcTag->commit();

			//todo 调用微信接口删除标签 失败则回滚
			$this->success('删除成功');
		}
		else{
			$this->assign('confirm_text','删除标签后，该标签下的所有用户将失去该标签属性。是否确定删除？');
			$this->assign('data',array('id'=>$id));
			$this->assign('posturl',U('ucuser/Ucuser/deleteUcusersTag'));
			$this->display('Public/popup');
		}
	}

	/*
	 * 设置备注名
	 */
	public function updateUserRemark()
	{
		$ids = input('ids','','intval');
		$name = input('name','','text');
		$model = model('Ucuser');
		$map['mp_id'] = get_mpid();
		$map['mid'] = $ids;
		$user_info = $model->where($map)->Field('remark,nickname,openid')->find();
		if(request()->isPost())
		{
			if(empty($user_info))
			{
				$this->error('用户不存在');
			}
			if(mb_strlen($name,'utf-8')>30)
			{
				$this->error('超过30个字符,'.mb_strlen($name,'utf-8').'/30');
			}
			$this->init_wx();
			$ret = $this->weObj->updateUserRemark($user_info['openid'],$name);
			if(!$ret)
			{
				$this->error('设置备注失败，错误：'.(ErrCode::getErrText($this->weObj->errCode)?ErrCode::getErrText($this->weObj->errCode):$this->weObj->errMsg));
			}
			$model->where($map)->save(array('remark'=>$name));
			$this->success('操作成功');
		}
		else
		{
			$inputs['text'] = array(
				array('key'=>'name','type'=>'text','name'=>'备注(30字内):','value'=>(empty($user_info['remark'])?$user_info['nickname']:$user_info['remark']))
			);
			$this->assign('confirm_text','设置备注');
			$this->assign('inputs',$inputs);
			$this->assign('data',array('ids'=>$ids));
			$this->assign('posturl',U('ucuser/Ucuser/updateUserRemark'));
			$this->display('Public/popup');
		}
	}

	/*
	 * 增加用户标签组
	 */
	public function updateUcuserTag()
	{
		$id = input('id','','int');
		$UcTag = model("Ucuser/UcuserTag");
		$map['mp_id'] = get_mpid();
		if(request()->isPost())
		{
			if(in_array($id,array('0','1','2')))
			{
				$this->error('这个标签默认保留的标签,不能修改',url('ucuser/Ucuser/ucuser_tag'));
			}
			$name = input('name','','text');
			$map['id'] = $id;
			$tagid = $UcTag->where($map)->find();
			if(empty($tagid))
			{
				$this->error('该公众号无此标签');
			}
			$tagid['name'] = $name;
			$data = $UcTag->create($tagid);
			if(!$data)
			{
				$this->error('操作失败，'.$UcTag->getError());
			}
			$this->init_wx();
			$ret = $this->weObj->updateTags($id,$name);
			if(!$ret)
			{
				$this->error('编辑公众号标签失败，错误：'.(ErrCode::getErrText($this->weObj->errCode)?ErrCode::getErrText($this->weObj->errCode):$this->weObj->errMsg));
			}
			$UcTag->save($data);
			$this->success('修改成功');
		}
		else
		{
			$map['id'] = $id;
			$value= $UcTag->where($map)->getfield('name');
			$inputs['text'] = array(
				array('key'=>'name','type'=>'text','name'=>'标签名称','value'=>$value)
			);
			$this->assign('confirm_text','编辑标签名');
			$this->assign('inputs',$inputs);
			$this->assign('data',array('id'=>$id));
			$this->assign('posturl',url('ucuser/Ucuser/updateUcuserTag'));
			$this->display('Public/popup');
		}
	}

	public function createUcuserTags()
	{
		$UcTag = model("Ucuser/UcuserTag");
		$map['mp_id'] = get_mpid();
		if(request()->isPost())
		{
			$name = input('name','','text');

			$map['name'] = $name;
			$tagid = $UcTag->where($map)->find();
			if(!empty($tagid))
			{
				$this->error('该标签已存在');
			}
			$this->init_wx();
			$ret = $this->weObj->createTags($name);
			if(!$ret)
			{
				$this->error('创建公众号标签失败，错误：'.(ErrCode::getErrText($this->weObj->errCode)?ErrCode::getErrText($this->weObj->errCode):$this->weObj->errMsg));
			}
			$ret['mp_id'] = get_mpid();
			$data = $UcTag->create($ret);
			if(!$data)
			{
				$this->error('操作失败，'.$UcTag->getError());
			}
			$UcTag->add($data);
			$this->success('修改成功');
		}
		else
		{
			$inputs['text'] = array(
				array('key'=>'name','type'=>'text','name'=>'标签名称','value'=>'')
			);
			$this->assign('confirm_text','新建标签名');
			$this->assign('inputs',$inputs);
			$this->assign('data',array(''=>""));
			$this->assign('posturl',U('ucuser/Ucuser/createUcuserTags'));
			$this->display('Public/popup');
		}
	}

	/*
	 * 同步公众号的用户标签列表
	 */
	public function sycUcuserTag()
	{


		$this->init_wx();
		$res = $this->weObj->getTags();
		if(!$res)
		{
			$this->error('获取公众号已创建的标签失败，错误：'.ErrCode::getErrText($this->weObj->errCode));
		}
		$UcTag = db("UcuserTag");
		foreach($res as &$r)
		{
			$r['mp_id'] = get_mpid();
			$UcTag->add_or_edit_tag($r);
			$ids[] = $r['id'];
		}
		$UcTag->where(array('mp_id'=>get_mpid(),'id'=>array('not in',$ids)))->delete();
		$this->success('获取公众号已创建的标签成功',url('ucuser/Ucuser/ucuser_tag'));
	}

}
