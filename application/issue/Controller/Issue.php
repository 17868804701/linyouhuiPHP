<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-11
 * Time: PM5:41
 */

namespace app\issue\controller;

use app\admin\controller\Admin;
use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminListBuilder;
use app\admin\builder\AdminTreeListBuilder;


class Issue extends Admin
{
    protected $issueModel;

    function _initialize()
    {
        $this->issueModel = model('Issue/Issue');
        parent::_initialize();
    }

    public function config()
    {
        $admin_config = new AdminConfigBuilder();
        $data = $admin_config->handleConfig();
        $data['NEED_VERIFY'] = $data['NEED_VERIFY'] ? $data['NEED_VERIFY'] : 0;
        $data['DISPLAY_TYPE'] = $data['DISPLAY_TYPE'] ? $data['DISPLAY_TYPE'] : 'list';
        $data['ISSUE_SHOW_TITLE'] = $data['ISSUE_SHOW_TITLE'] ? $data['ISSUE_SHOW_TITLE'] : lang('_ISSUE_HOTTEST_');
        $data['ISSUE_SHOW_COUNT'] = $data['ISSUE_SHOW_COUNT'] ? $data['ISSUE_SHOW_COUNT'] : 4;
        $data['ISSUE_SHOW_ORDER_FIELD'] = $data['ISSUE_SHOW_ORDER_FIELD'] ? $data['ISSUE_SHOW_ORDER_FIELD'] : 'view_count';
        $data['ISSUE_SHOW_ORDER_TYPE'] = $data['ISSUE_SHOW_ORDER_TYPE'] ? $data['ISSUE_SHOW_ORDER_TYPE'] : 'desc';
        $data['ISSUE_SHOW_CACHE_TIME'] = $data['ISSUE_SHOW_CACHE_TIME'] ? $data['ISSUE_SHOW_CACHE_TIME'] : '600';
        $admin_config->title(lang('_ISSUE_BASIC_SETTINGS_'))
            ->keyBool('NEED_VERIFY', lang('_AUDIT_CONTRIBUTE_'), lang('_AUDIT_DEFAULT_NO_NEED_'))
            ->keyRadio('DISPLAY_TYPE', lang('_DISPLAY_DEFAULT_'), lang('_DISPLAY_DEFAULT_VICE_'),array('list'=>lang('_LIST_'),'masonry'=>lang('_MASONRY_')))
            ->buttonSubmit('', lang('_SAVE_'))->data($data);
        $admin_config->keyText('ISSUE_SHOW_TITLE', lang('_TITLE_NAME_'), lang('_TITLE_NAME_VICE_'));
        $admin_config->keyText('ISSUE_SHOW_COUNT', lang('_ISSUE_SHOW_NUMBER_'), lang('_ISSUE_SHOW_NUMBER_VICE_'));
        $admin_config->keyRadio('ISSUE_SHOW_ORDER_FIELD', lang('_SORT_VALUE_'), lang('_TIP_SORT_TYPE_'), array('view_count' => lang('_VIEWS2_'), 'reply_count' => lang('_REPLIES_'), 'create_time' => lang('_PUBLISH_TIME_'), 'update_time' => lang('_UPDATE_TIME_')));
        $admin_config->keyRadio('ISSUE_SHOW_ORDER_TYPE', lang('_SORT_TYPE_'), lang('_TIP_SORT_TYPE_'), array('desc' => lang('_COUNTER_'), 'asc' => lang('_DIRECT_')));
        $admin_config->keyText('ISSUE_SHOW_CACHE_TIME', lang('_CACHE_TIME_'), lang('_TIP_CACHE_TIME_'));
        $admin_config->group(lang('_BASIC_CONF_'), 'NEED_VERIFY,DISPLAY_TYPE')->group(lang('_HOME_SHOW_CONF_'), 'ISSUE_SHOW_COUNT,ISSUE_SHOW_TITLE,ISSUE_SHOW_ORDER_TYPE,ISSUE_SHOW_ORDER_FIELD,ISSUE_SHOW_CACHE_TIME');

        $admin_config->groupLocalComment(lang('_LOCAL_COMMENT_CONF_'),'issueContent');



        $admin_config->display();
    }

    public function issue()
    {
        //显示页面
        $builder = new AdminTreeListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';
        $attr1 = $attr;
        $attr1['url'] = $builder->addUrlParam(url('setWeiboTop'), array('top' => 1));
        $attr0 = $attr;
        $attr0['url'] = $builder->addUrlParam(url('setWeiboTop'), array('top' => 0));
        $tree = model('Issue/Issue')->getTree(0, 'id,title,sort,pid,status');
        $builder->title(lang('_ISSUE_MANAGE_'))
            ->buttonNew(url('Issue/add'))
            ->data($tree)
            ->display();
    }

    public function add($id = 0, $pid = 0)
    {
        if (IS_POST) {
            if ($id != 0) {
                $issue = $this->issueModel->create();
                if ($this->issueModel->save($issue)) {
                    $this->success(lang('_SUCCESS_EDIT_'));
                } else {
                    $this->error(lang('_FAIL_EDIT_'));
                }
            } else {
                $issue = $this->issueModel->create();
                if ($this->issueModel->add($issue)) {

                    $this->success(lang('_SUCCESS_ADD_'));
                } else {
                    $this->error(lang('_FAIL_ADD_'));
                }
            }


        } else {
            $builder = new AdminConfigBuilder();
            $issues = $this->issueModel->select();
            $opt = array();
            foreach ($issues as $issue) {
                $opt[$issue['id']] = $issue['title'];
            }
            if ($id != 0) {
                $issue = $this->issueModel->find($id);
            } else {
                $issue = array('pid' => $pid, 'status' => 1);
            }


            $builder->title(lang('_CATEGORY_ADD_'))->keyId()->keyText('title', lang('_TITLE_'))->keySelect('pid',lang('_FATHER_CLASS_'), lang('_FATHER_CLASS_SELECT_'), array('0' =>lang('_TOP_CLASS_')) + $opt)
                ->keyStatus()->keyCreateTime()->keyUpdateTime()
                ->data($issue)
                ->buttonSubmit(url('Issue/add'))->buttonBack()->display();
        }

    }

    public function issueTrash($page = 1, $r = 20, $model = '')
    {
        $builder = new AdminListBuilder();
        $builder->clearTrash($model);
        //读取微博列表
        $map = array('status' => -1);
        $model = $this->issueModel;
        $list = $model->where($map)->page($page, $r)->select();
        $totalCount = $model->where($map)->count();

        //显示页面

        $builder->title(lang('_ISSUE_TRASH_'))
            ->setStatusUrl(url('setStatus'))->buttonRestore()->buttonClear('Issue/Issue')
            ->keyId()->keyText('title', lang('_TITLE_'))->keyStatus()->keyCreateTime()
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function operate($type = 'move', $from = 0)
    {
        $builder = new AdminConfigBuilder();
        $from = model('Issue')->find($from);

        $opt = array();
        $issues = $this->issueModel->select();
        foreach ($issues as $issue) {
            $opt[$issue['id']] = $issue['title'];
        }
        if ($type === 'move') {

            $builder->title(lang('_CATEGORY_MOVE_'))->keyId()->keySelect('pid',lang('_FATHER_CLASS_'), lang('_FATHER_CLASS_SELECT_'), $opt)->buttonSubmit(url('Issue/add'))->buttonBack()->data($from)->display();
        } else {

            $builder->title(lang('_CATEGORY_COMBINE_'))->keyId()->keySelect('toid', lang('_CATEGORY_T_COMBINE_'), lang('_CATEGORY_T_COMBINE_SELECT_'), $opt)->buttonSubmit(url('Issue/doMerge'))->buttonBack()->data($from)->display();
        }

    }

    public function doMerge($id, $toid)
    {
        $effect_count = model('IssueContent')->where(array('issue_id' => $id))->setField('issue_id', $toid);
        model('Issue')->where(array('id' => $id))->setField('status', -1);
        $this->success(lang('_SUCCESS_CATEGORY_COMBINE_') . $effect_count . lang('_CONTENT_GE_'), url('issue'));
        //TODO 实现合并功能 issue
    }

    public function contents($page = 1, $r = 10)
    {
        //读取列表
        $map = array('status' => 1);
        $model = db('IssueContent');
        $list = $model->where($map)->select();
        unset($li);
        $totalCount = $model->where($map)->count();

        //显示页面
        $builder = new AdminListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';


        $builder->title(lang('_CONTENT_MANAGE_'))
            ->setStatusUrl(url('setIssueContentStatus'))->buttonDisable('', lang('_AUDIT_UNSUCCESS_'))->buttonDelete()
            ->keyId()->keyLink('title', lang('_TITLE_'), 'Issue/Index/issueContentDetail?id=###')->keyUid()->keyCreateTime()->keyStatus()
            ->data($list)
            ->pagination($totalCount, $r);
        return $builder->fetch();
    }

    public function verify($page = 1, $r = 10)
    {
        //读取列表
        $map = array('status' => 0);
        $model = db('IssueContent');
        $list = $model->where($map)->select();
        unset($li);
        $totalCount = $model->where($map)->count();

        //显示页面
        $builder = new AdminListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';


        return $builder->title(lang('_CONTENT_AUDIT_'))
            ->setStatusUrl(url('setIssueContentStatus'))->buttonEnable('', lang('_AUDIT_SUCCESS_'))->buttonDelete()
            ->keyId()->keyLink('title', lang('_TITLE_'), 'Issue/Index/issueContentDetail?id=###')->keyUid()->keyCreateTime()->keyStatus()
            ->data($list)
            ->pagination($totalCount, $r)
            ->fetch();
    }

    public function setIssueContentStatus()
    {
        $ids = input('ids');
        $status = input('get.status', 0, 'intval');
        $builder = new AdminListBuilder();
        if ($status == 1) {
            foreach ($ids as $id) {
                $content = model('IssueContent')->find($id);
                model('Common/Message')->sendMessage($content['uid'],$title = lang('_MESSAGE_AUDIT_ISSUE_CONTENT_'), lang('_MESSAGE_AUDIT_ISSUE_CONTENT_VICE_'),  'Issue/Index/issueContentDetail', array('id' => $id), is_login(), 2);
                /*同步微博*/
                /*  $user = query_user(array('nickname', 'space_link'), $content['uid']);
                  $weibo_content = '管理员审核通过了@' . $user['nickname'] . ' 的内容：【' . $content['title'] . '】，快去看看吧：' ."http://$_SERVER[HTTP_HOST]" .url('Issue/Index/issueContentDetail',array('id'=>$content['id']));
                  $model = model('Weibo/Weibo');
                  $model->addWeibo(is_login(), $weibo_content);*/
                /*同步微博end*/
            }

        }
        $builder->doSetStatus('IssueContent', $ids, $status);

    }

    public function contentTrash($page = 1, $r = 10, $model = '')
    {
        //读取微博列表
        $builder = new AdminListBuilder();
        $builder->clearTrash($model);
        $map = array('status' => -1);
        $model = db('IssueContent');
        $list = $model->where($map)->page($page, $r)->select();
        $totalCount = $model->where($map)->count();

        //显示页面

        return $builder->title(lang('_CONTENT_TRASH_'))
            ->setStatusUrl(url('setIssueContentStatus'))->buttonRestore()->buttonClear('IssueContent')
            ->keyId()->keyLink('title', lang('_TITLE_'), 'Issue/Index/issueContentDetail?id=###')->keyUid()->keyCreateTime()->keyStatus()
            ->data($list)
            ->pagination($totalCount, $r)
            ->fetch();
    }
}
