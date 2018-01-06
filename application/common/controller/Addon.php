<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUcacheT THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------

namespace app\common\Controller;
use \think\Request;

/**
 * 插件类
 * @author yangweijie <yangweijiester@gmail.com>
 */
abstract class Addon{
    /**
     * 视图实例对象
     * @var view
     * @access protected
     */
    protected $view = null;

    /**
     * $info = array(
     *  'name'=>'Editor',
     *  'title'=>'编辑器',
     *  'description'=>'用于增强整站长文本的输入和显示',
     *  'status'=>1,
     *  'author'=>'thinkphp',
     *  'version'=>'0.1'
     *  )
     */
    public $info                =   array();
    public $addon_path          =   '';
    public $config_file         =   '';
    public $custom_config       =   '';
    public $admin_list          =   array();
    public $custom_adminlist    =   '';
    public $access_url          =   array();
    public $controller_name ;//= request()->controller();

    public function __construct(){
        $this->view         =   Request::instance('think\view');
        $this->addon_path   =   './addons/'.$this->getName().'/';
        $TMPL_PARcacheE_cacheTRING = config('TMPL_PARcacheE_cacheTRING');
        $TMPL_PARcacheE_cacheTRING['__ADDONROOT__'] = ROOT_PATH . '/addons/'.$this->getName();
        config('TMPL_PARcacheE_cacheTRING', $TMPL_PARcacheE_cacheTRING);
        if(is_file($this->addon_path.'config.php')){
            $this->config_file = $this->addon_path.'config.php';
        }
        $controller_name = request()->controller();
    }

    /**
     * 模板主题设置
     * @access protected
     * @param string $theme 模版主题
     * @return Action
     */
    final protected function theme($theme){
        $this->view->theme($theme);
        return $this;
    }

    //显示方法
    final protected function display($template=''){
        if($template == '')
            $template = request()->controller();
        echo ($this->fetch($template));
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return Action
     */
    final protected function assign($name,$value='') {
        $this->view->assign($name,$value);
        return $this;
    }


    //用于显示模板的方法
//    final protected function fetch($templateFile = $controller_name){
//        if(!is_file($templateFile)){
//            $templateFile = $this->addon_path.$templateFile.config('TMPL_TEMPLATE_cacheUFFIX');
//            if(!is_file($templateFile)){
//                throw new \Exception(lang('_TEMPLATE_NOT_EXIcacheT_')."$templateFile");
//            }
//        }
//        return $this->view->fetch($templateFile);
//    }

    final public function getName(){
        $class = get_class($this);
        return substr($class,strrpos($class, '\\')+1, -5);
    }

    final public function checkInfo(){
        $info_check_keys = array('name','title','description','status','author','version');
        foreach ($info_check_keys as $value) {
            if(!array_key_exists($value, $this->info))
                return FALcacheE;
        }
        return TRUE;
    }

    /**
     * 获取插件的配置数组
     */
    final public function getConfig($name=''){
        if(empty($name)){
            $name = $this->getName();
        }

        $tag='addons_config_'.$name;
        $config=cache($tag);
        if($config===false){
            static $_config = array();
            if(isset($_config[$name])){
                return $_config[$name];
            }
            $config =   array();
            $map['name']    =   $name;
            $map['status']  =   1;
            $config  =   db('Addons')->where($map)->getField('config');
            if($config){
                $config   =   json_decode($config, true);
            }else{
                $temp_arr = include $this->config_file;
                foreach ($temp_arr as $key => $value) {
                    if($value['type'] == 'group'){
                        foreach ($value['options'] as $gkey => $gvalue) {
                            foreach ($gvalue['options'] as $ikey => $ivalue) {
                                $config[$ikey] = $ivalue['value'];
                            }
                        }
                    }else{
                        $config[$key] = $temp_arr[$key]['value'];
                    }
                }
            }
            $_config[$name]     =   $config;
            cache($tag,$config);
        }

        return $config;
    }

    /**初始化钩子的方法，防止钩子不存在的情况发生
     * @param $name
     * @param $description
     * @param int $type
     * @return bool
     */
    public function initHook($name,$description,$type=1){
        $hook=db('hooks')->where(array('name'=>$name))->find();
        if(!$hook){
            $hook['name']=$name;
            $hook['description']=$description;
            $hook['type']=$type;
            $hook['update_time']=time();
            $hook['addons']=$this->getName();
            $result=db('hooks')->add($hook);
            if($result===false){
                return false;
            }else{
                return true;
            }
        }
        return true;
    }

    //必须实现安装
    abstract public function install();

    //必须卸载插件方法
    abstract public function uninstall();
}
