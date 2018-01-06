<?php
$dirs = array('../runtime/cache/','../runtime/temp/');

//清文件缓存
//$dirs	=	array($dirname);


header('Content-Type:text/html;charset=utf-8');
//清理缓存
foreach($dirs as $value) {
    rmdirr($value);
    @mkdir($value,0777,true);
    echo "".$value."\" 已经被删除!缓存清理完毕。 ";
}



function rmdirr($dirname) {
    if (!file_exists($dirname)) {
        return false;
    }
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }
    $dir = dir($dirname);
    if($dir){
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
        }
    }
    $dir->close();
    return rmdir($dirname);
}
?>