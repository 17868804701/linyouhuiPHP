<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-13
 * Time: 下午2:05
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace app\admin\controller;

use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminListBuilder;
use app\admin\builder\AdminSortBuilder;
use app\admin\builder\AdminTreeListBuilder;

class UserTag extends Admin
{
    protected $userTagModel;

    public function _initialize()
    {
        parent:: _initialize();
        $this->userTagModel=db('Ucenter/UserTag');
    }
    /**
     * 标签分类
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function userTag()
    {
        //显示页面
        $builder = new AdminTreeListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';

        $tree = $this->userTagModel->getTree(0, 'id,title,sort,pid,status');

        $builder->title(lang('_USER_TAG_MANAGER_'))
            ->suggest(lang('_USER_TAG_MANAGER_VICE_'))
            ->buttonNew(U('UserTag/add'))->button(lang('_RECYCLE_BIN_'),array('href'=>U('UserTag/TagTrash')))
            ->data($tree)
            ->display();
    }

    /**
     * 分类添加
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function add($id=0,$pid=0)
    {
        if (IS_POST) {
            if ($id != 0) {
                $result=$this->userTagModel->saveData();
                if ($result) {
                    $this->success(lang('_SUCCESS_EDIT_').lang('_PERIOD_'), U('UserTag/userTag'));
                } else {
                    $this->error(lang('_FAIL_EDIT_').lang('_PERIOD_').$this->userTagModel->getError());
                }
            } else {
                $result=$this->userTagModel->addData();
                if ($result) {
                    $this->success(lang('_SUCCESS_ADD_').lang('_PERIOD_'));
                } else {
                    $this->error(lang('_FAIL_ADD_').lang('_PERIOD_').$this->userTagModel->getError());
                }
            }
        } else {
            $builder = new AdminConfigBuilder();
            $opt = array();
            if ($id != 0) {
                $category = $this->userTagModel->find($id);
                if($category['pid']!=0){
                    $categorys = $this->userTagModel->where(array('pid'=>0))->select();
                    foreach ($categorys as $cate) {
                        $opt[$cate['id']] = $cate['title'];
                    }
                }
            } else {
                $category = array('pid' => $pid, 'status' => 1);
                $father_category_pid=$this->userTagModel->where(array('id'=>$pid))->getField('pid');
                if($father_category_pid!=0){
                    $this->error(lang('_ERROR_CATEGORY_HIR_LIMIT_').lang('_EXCLAMATION_'));
                }
                $categorys = $this->userTagModel->where(array('pid'=>0))->select();
                foreach ($categorys as $cate) {
                    $opt[$cate['id']] = $cate['title'];
                }
            }
            if($pid!=0){
                $builder->title(lang('_TAG_ADD_'));
            }else{
                $builder->title(lang('_CATEGORY_ADD_'));
            }
            $builder->keyId()->keyText('title', lang('_TITLE_'))->keySelect('pid', lang('_FATHER_CLASS_'), lang('_FATHER_CLASS_SELECT_'), array('0' => lang('_TOP_CLASS_')) + $opt)
                ->keyStatus()
                ->data($category)
                ->buttonSubmit(U('UserTag/add'))->buttonBack()->display();
        }

    }

    /**
     * 分类回收站
     * @param int $page
     * @param int $r
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function tagTrash($page = 1, $r = 20)
    {
        $builder = new AdminListBuilder();
        //读取微博列表
        $map = array('status' => -1);
        $list = $this->userTagModel->where($map)->page($page, $r)->select();
        $totalCount = $this->userTagModel->where($map)->count();

        //显示页面

        $builder->title(lang('_TRASH_TAG_CATEGORY_'))
            ->setStatusUrl(U('setStatus'))->buttonRestore()->buttonDeleteTrue(U('UserTag/userTagClear'))
            ->keyId()->keyText('title', lang('_TITLE_'))->keyText('pid',lang('_ID_CATEGORY_FATHER_'))
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function userTagClear($ids)
    {
        $builder=new AdminListBuilder();
        $builder->doDeleteTrue('UserTag',$ids);
    }

    /**
     * 设置商品分类状态：删除=-1，禁用=0，启用=1
     * @param $ids
     * @param $status
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function setStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        if($status==-1){
            $id = array_unique((array)$ids);
            $rs=M('UserTag')->where(array('pid' => array('in', $id)))->save(array('status' => $status));
        }
        $builder->doSetStatus('UserTag', $ids, $status);
    }

    //分类、标签end
} 