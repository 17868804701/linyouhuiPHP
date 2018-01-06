<?php
/**
 * Created by PhpStorm.
 * User: UCT
 * Date: 2017/2/23
 * Time: 15:41
 */
namespace app\common\model;
use think\Model;

class Picture extends Model{

    //	上传文件
    public function picture($file){
        // 获取表单上传文件 例如上传了001.jpg
//        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $path = 'uploads/picture/';
        $info = $file->rule('uniqid')->move($path);
        if($info){
            // 成功上传后 获取上传信息

            $data= array(
                'status'=>1,
                'type'=>$info->getExtension(),//获取类型
                'path'=>'/uploads/picture/'.$info->getFilename(),
                'url'=>request()->domain(). '/uploads/picture/'.$info->getFilename(),
                'create_time'=>time(),
            );
            $ret = $this->save($data);
            if ($ret){
                $data['id'] = $this->id;
                //echo json_encode($data);//var_dump($info);
                return $data['id'];
            }

        }else{
            // 上传失败获取错误信息
            return 0;
        }
    }
}