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
namespace app\ucenter\logic;

use think\Model;

class UcenterMember extends Model
{
    //

    /**
     * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type 用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function login($username, $password, $type = 1)
    {

        $map = array();
        switch ($type) {
            case 1:
                $map['username'] = $username;
                break;
            case 2:
                $map['email'] = $username;
                break;
            case 3:
                $map['mobile'] = $username;
                break;
            case 4:
                $map['id'] = $username;
                break;
            default:
                return 0; //参数错误
        }
        /* 获取用户数据 */
        $ucModel = new \app\ucenter\model\UcenterMember;
        $user = $ucModel->where($map)->find();
        //TODO:操作限制检测
        /*
                $return = check_action_limit('input_password','ucenter_member',$user['id'],$user['id']);
                if($return && !$return['state']){
                    return $return['info'];
                }
                */

        if (is_object($user) && $user['status']) {
            /* 验证用户密码 */

            if (think_ucenter_md5($password, UC_AUTH_KEY) === $user['password']) {

                $data['id'] = $user['id'];
                //更新登录信息
                $data['last_login_time']  = $_SERVER['REQUEST_TIME'];
                $data['last_login_ip']  = get_client_ip(1);

                $ucModel->isUpdate(true)->save($data);  // 显式指定更新数据操作
                return $user['id']; //登录成功，返回用户ID
            } else {
              //TODO:行为日志  action_log('input_password','ucenter_member',$user['id'],$user['id']);
                return -2; //密码错误
            }
        } else {
            return -1; //用户不存在或被禁用
        }

        //TODO:Ucenter同步登录
        /*
        if (UC_SYNC && $user['id'] != 1) {
            return $this->ucLogin($username, $password);
        }
        */
    }

    /**
     * 注册一个新用户
     * @param  string $username 用户名
     * @param  string $nickname 昵称
     * @param  string $password 用户密码
     * @param  string $email 用户邮箱
     * @param  string $mobile 用户手机号码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     */
    public function register($username, $nickname, $password, $email='', $mobile='', $type=1)
    {
        $data = array(
            'username' => $username,
            'password' => think_ucenter_md5($password, UC_AUTH_KEY),
            'email' => $email,
            'mobile' => $mobile,
            'type' => $type,
        );

        //验证手机
        if (empty($data['mobile'])) unset($data['mobile']);
        if (empty($data['username'])) unset($data['username']);
        if (empty($data['email'])) unset($data['email']);
        if (empty($nickname)) {   //没有提供昵称就默认为手机号码
            $nickname = $mobile;
        };

        /* 添加用户 */
        $usercenter_member = $data;
        if ($usercenter_member) {
            $result = model('Member')->registerMember($nickname);
            if ($result > 0) {
                $usercenter_member['id'] = $result;
                $ucModel = new \app\ucenter\model\UcenterMember();
                $id = $ucModel->save($usercenter_member);
                trace($id,'register save after');
                if ($id === false) {
                    //如果注册失败，则回去Memeber表删除掉错误的记录
                    model('Member')->where(array('uid' => $result))->delete();
                }
                // action_log('reg','ucenter_member',1,1);
                return $id ? $id : 0; //0-未知错误，大于0-注册成功
            } else {
                return $result;
            }
        } else {
            return $this->getError(); //错误详情见自动验证注释
        }
    }

    /**
     * 初始化角色用户信息
     * @param $role_id
     * @param $uid
     * @return bool
     * @author 郑钟良<zzl@ourstu.com>
     */
    public  function initRoleUser($role_id = 0, $uid)
    {
        $memberModel = model('Member');
        $role = db('Role')->where(array('id' => $role_id))->find();
        $user_role = array('uid' => $uid, 'role_id' => $role_id, 'step' => "start");
        if ($role['audit']) { //该角色需要审核
            $user_role['status'] = 2; //未审核
        } else {
            $user_role['status'] = 1;
        }
        $result = db('UserRole')->insert($user_role);
        if (!$role['audit']) {
            //该角色不需要审核
            $memberModel->initUserRoleInfo($role_id, $uid);
        }
        $memberModel->initDefaultShowRole($role_id, $uid);

        return $result;
    }

    /**
     * 更新用户信息，带密码验证
     * @param int    $uid 用户id
     * @param string $password 密码，用来验证
     * @param array  $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author Patrick <contact@uctoo.com>
     */
    public function updateUserFieldsPwd($uid, $password, $data)
    {
        if (empty($uid) || empty($password) || empty($data)) {
            $this->error = lang('_PARAM_ERROR_25_');
            return false;
        }

        //更新前检查用户密码
        if (!$this->verifyUser($uid, $password)) {
            $this->error = lang('_VERIFY_ERROR_PW_WRONG_');
            return false;
        }

        //更新用户信息
        if ($data) {
            if (array_key_exists("password", $data)) { //要修改的字段里带新密码就把新密码加密
                $data['password'] = think_ucenter_md5($data['password'],UC_AUTH_KEY);
            }
            return $this->where(array('id' => $uid))->update($data);
        }
        return false;
    }

    /**
     * 更新用户信息
     * @param int    $uid 用户id
     * @param array  $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author Patrick <contact@uctoo.com>
     */
    public function updateUserFields($uid, $data)
    {
        if (empty($uid) || empty($data)) {
            $this->error = lang('_PARAM_ERROR_25_');
            return false;
        }
        //更新用户信息
        if ($data) {
            return $this->where(array('id' => $uid))->update($data);
        }
        return false;
    }

    /**
     * 验证用户密码
     * @param int    $uid 用户id
     * @param string $password_in 密码
     * @return true 验证成功，false 验证失败
     * @author Patrick <contact@uctoo.com>
     */
    public function verifyUser($uid, $password_in)
    {
        $ucModel = new \app\ucenter\model\UcenterMember;
        $user = $ucModel->get($uid);
        $password = $user->password;
        if (think_ucenter_md5($password_in, UC_AUTH_KEY) === $password) {
            return true;
        }
        return false;
    }

}
