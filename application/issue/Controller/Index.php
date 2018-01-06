<?php


namespace app\issue\controller;

use app\admin\controller\Admin;
use think\Controller;
use app\admin\builder\AdminConfigBuilder;
use think\Request;


class Index extends Controller
{
    /**
     * 压缩html : 清除换行符,清除制表符,去掉注释标记
     * @param $string
     * @return  压缩后的$string
     * */
    function compress_html($string) {
        $string = str_replace("\r\n", '', $string); //清除换行符
        $string = str_replace("\n", '', $string); //清除换行符
        $string = str_replace("\t", '', $string); //清除制表符
        $pattern = array (
            "/> *([^ ]*) *</", //去掉注释标记
            "/[\s]+/",
            "/<!--[^!]*-->/",
            "/\" /",
            "/ \"/",
            "'/\*[^*]*\*/'"
        );
        $replace = array (
            ">\\1<",
            " ",
            "",
            "\"",
            "\"",
            ""
        );
        return preg_replace($pattern, $replace, $string);
    }

    function getImage($url){
        for ($pageno = 1 ; $pageno < 1848; $pageno ++) {
            $content = file_get_contents('http://www.haha.mx/topic/1/new/'.$pageno);
            preg_match_all('/class=\"joke-main-img\" src=\"(.*?)\"/',$content,$matches);
            foreach ($matches[1] as $url) {
                $url = str_replace('small','big',$url);
                $img = file_get_contents($url);
                file_put_contents('./save/'.basename($url),$img);
            }
        }
    }


    function index(){
        if (request()->isPost()){
            $domain = Request::instance()->domain();
            $param = input('post.');
            $url = input('urls');
            $content = $this->curl_request($url,'get',Request::instance()->header());
//            $content = file_get_contents($url);
//            echo $content;

//            $qian=array(" ","　","\t","\n","\r");
//            $hou=array("","","","","");
//            $content = str_replace($qian,$hou,$content);

//            $content = $this->compress_html($content);
            trace($content,'内容');

            //ziduan
            $search1 = $param['pre_tag1'].'(.*)'.$param['end_tag1'];
            $search2 = $param['pre_tag2'].'(.*)'.$param['lst_tag2'];
            $search3 = $param['pre_tag3'].'(.*)'.$param['lst_tag3'];

            //页面类似
            $list = array();
            if ($param['type'] == 1){
                //列表页
//                $suburl = 'href=\"(.*)'.$param['suburl'].'([^\"]*)\"';
                $suburl = $param['suburl'].'([^\"]*)\/';
                echo $suburl;
                preg_match_all("#$suburl#",$content,$list);
//                trace($list,'列表');
//                $list = array_unique($list[0]);
//                foreach ($list as $val ){
//                    $data = $this->curl_request($val,'get',Request::instance()->header());
//                    $con = $this->compress_html($data);
//                    $data = preg_match("/$search1/",$content,$match);
//                    dump($match);
//                    echo '<br>结果'.strip_tags($match[1]);
//                }
                dump($list);
            }else{
                //内容页
                echo '<br>search->'.$search1.'<br>';
                //存数组
                $data = array();
                $data = preg_match("/$search1/",$content,$match);
                dump($match);
                echo '<br>结果'.strip_tags($match[1]);
            }
        }else{
            $build = new AdminConfigBuilder();
            $build->title('数据爬虫')
                ->keyText('url','网站')
                ->keyText('sub_url','下一级标识')
                ->keyText('text1','字段1')
                ->keyText('text2','字段')
                ->buttonSubmit('');
            return $this->fetch();
//            return $build->fetch();
        }
    }

    /**
     * [attr description]
     * @param  [type]  $url     url
     * @param  [type]  $method  post or get
     * @param  [type]  $data    request data
     * @param  [type]  $header  request header
     * @param  [type]  $timeout timeout
     * @return [type]  return   result
     */
    function curl_request($url,$method,$header,$timeout=10){
        if(!$url)return false;
//        $urlInfo=$this->parseUrl($url);
        if($method=="get"){
            $conn= curl_init($url);
            curl_setopt($conn, CURLOPT_HEADER,0);
            curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($conn, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($conn, CURLOPT_HTTPHEADER, $header);
            $content=curl_exec($conn);
            curl_close($conn);
        }elseif($method=="post"){
            $conn= curl_init($url);
//            $data&&$data=$this->formatUrl($data);
            curl_setopt($conn, CURLOPT_HEADER,0);
            curl_setopt($conn, CURLOPT_POST, true);
            curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($conn, CURLOPT_CONNECTTIMEOUT, $timeout);
//            $data&&curl_setopt($conn, CURLOPT_POSTFIELDS, $data);
            curl_setopt($conn, CURLOPT_HTTPHEADER, $header);
            $content=curl_exec($conn);
            curl_close($conn);
        }
        return $content;
    }
    /**
     * [attr description]
     * @param  [type]  $url url
     * @return [type]  return result(array)
     */
    function parseUrl($url){
        $username="";$password="";$scheme=@explode("//", $url)[0]?explode(":", $url)[0]:"http";
        $port=@preg_match_all("/.*?\:(\d+).*?/", $url, $matches)?$matches[1][0]:"80";
        $path=@preg_match_all("/\/.*?\/.*?\/{1,}.*?/", @explode("//",$url)[1], $pathTmp)?$pathTmp[0][0]:"/";
        $hash=@explode("#", $url)[1]?@explode("#", $url)[1]:"";
        $queryString=@explode("?", $url)[1]?@explode("?", $url)[1]:"";
        $filename=@preg_match_all("/.*?\/([^\/]*?)\?.*?/", @explode("//", $url)[1], $matches)?$matches[1][0]:preg_match("/.*?(\/.*)/", explode("//", $url)[1], $matches)?$matches[1]:"/";
        if (stristr(explode("?", $url)[0], "@")) {
            $username=@explode(":", @explode("//", $url)[1])[0]?@explode(":", @explode("//", $url)[1])[0]:"";
            $password=@explode(":",@explode("@", @explode("//", $url)[1])[0])[1]?@explode(":",@explode("@", @explode("//", $url)[1])[0])[1]:"";
            $host=@explode(":", @explode("/", @explode('@', @explode("?", $url)[0])[1])[0])[0];
        }else $host=explode(":", preg_match("/.*?\/\/([^\/]*+)/", $url, $matches)?$matches[1]:"")[0];
        return array(
            "scheme"=>$scheme,
            "host"=>$host,
            "port"=>$port,
            "username"=>$username,
            "password"=>$password,
            "path"=>$path,
            "filename"=>$filename,
            "queryString"=>$queryString,
            "hash"=>$hash
        );
    }
    /**
     * [attr description]
     * @param  [type]  $ar array or string
     * @return [type]  return result(array or string)
     */
    function formatUrl($arg){
        if(is_array($arg)){
            $str='';
            foreach ($arg as $key => $value)$str.=$key."=".$value."&";
            return rtrim($str,"&");
        }else{
            $array=array();$arg=explode("&", $arg);
            foreach ($arg as $value)$value&&$array=array_merge($array,array(explode("=",$value)[0]=> explode("=",$value)[1]));
            return $array;
        }
    }
    /**
     * [attr description]
     * @param  [type]  $flag default false
     * @return [type]  return htmlcontont or echo htmlcontent
     */
    function getHtmlContent($flag=false){
        if($flag)return $this->htmlContent;
        echo "<pre>";
        echo htmlspecialchars($this->htmlContent);
        echo "</pre>";
    }
}