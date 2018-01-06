<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 4/2/14
 * Time: 10:27 AM
 */

if(!function_exists('boolval')) {
    function boolval($x) {
        return $x ? true : false;
    }
}

function arrayval($x) {
    return is_array($x) ? $x : array();
}

// 不区分大小写的in_array实现
function in_array_case($value, $array)
{
    return in_array(strtolower($value), array_map('strtolower', $array));
}

if (!function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null)
    {
        $result = array();
        if (null === $indexKey) {
            if (null === $columnKey) {
                $result = array_values($input);
            } else {
                foreach ($input as $row) {
                    $result[] = $row[$columnKey];
                }
            }
        } else {
            if (null === $columnKey) {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row;
                }
            } else {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row[$columnKey];
                }
            }
        }
        return $result;
    }
}

/**
 * select返回的数组进行整数映射转换
 *
 * @param array $map 映射关系二维数组  array(
 *                                          '字段名1'=>array(映射关系数组),
 *                                          '字段名2'=>array(映射关系数组),
 *                                           ......
 *                                       )
 * @author 朱亚杰 <zhuyajie@topthink.net>
 * @return array
 *
 *  array(
 *      array('id'=>1,'title'=>'标题','status'=>'1','status_text'=>'正常')
 *      ....
 *  )
 *
 */
function int_to_string(&$data, $map = array('status' => array(1 => '正常', -1 => '删除', 0 => '禁用', 2 => '未审核', 3 => '草稿')))
{
    if ($data === false || $data === null) {
        return $data;
    }
    $data = (array)$data;
    foreach ($data as $key => $row) {
        foreach ($map as $col => $pair) {
            if (isset($row[$col]) && isset($pair[$row[$col]])) {
                $data[$key][$col . '_text'] = $pair[$row[$col]];
            }
        }
    }
    return $data;
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list, $field, $sortby = 'asc')
{
    if (is_array($list)) {
        $refer = $resultSet = array();
        foreach ($list as $i => $data)
            $refer[$i] = &$data[$field];
        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc': // 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val)
            $resultSet[] = &$list[$key];
        return $resultSet;
    }
    return false;
}

/**
 * 取一个二维数组中的每个数组的固定的键知道的值来形成一个新的一维数组
 * @param $pArray 一个二维数组
 * @param $pKey 数组的键的名称
 * @return 返回新的一维数组
 */
function getSubByKey($pArray, $pKey = "", $pCondition = "")
{
    $result = array();
    if (is_array($pArray)) {
        foreach ($pArray as $temp_array) {
            if (is_object($temp_array)) {
                $temp_array = (array)$temp_array;
            }
            if (("" != $pCondition && $temp_array[$pCondition[0]] == $pCondition[1]) || "" == $pCondition) {
                $result[] = ("" == $pKey) ? $temp_array : isset($temp_array[$pKey]) ? $temp_array[$pKey] : "";
            }
        }
        return $result;
    } else {
        return false;
    }
}

/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 * @author huajie <banhuajie@163.com>
 */
function time_format($time = NULL, $format = 'Y-m-d H:i')
{
    $time = $time === NULL ? $_SERVER['REQUEST_TIME'] : intval($time);
    return date($format, $time);
}

/**
 * 	作用：array转xml
 */
function arrayToXml($arr)
{
    $xml = "<xml>";
    foreach ($arr as $key=>$val)
    {
        if (is_numeric($val))
        {
            $xml.="<".$key.">".$val."</".$key.">";

        }
        else
            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    }
    $xml.="</xml>";
    return $xml;
}

/**
 * 	作用：将xml转为array
 */
function xmlToArray($xml)
{
    //将XML转为array
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}

// 把字符串转成数组，支持汉字，只能是utf-8格式的
function StringToArray($str) {
    $result = array ();
    $len = strlen ( $str );
    $i = 0;
    while ( $i < $len ) {
        $chr = ord ( $str [$i] );
        if ($chr == 9 || $chr == 10 || (32 <= $chr && $chr <= 126)) {
            $result [] = substr ( $str, $i, 1 );
            $i += 1;
        } elseif (192 <= $chr && $chr <= 223) {
            $result [] = substr ( $str, $i, 2 );
            $i += 2;
        } elseif (224 <= $chr && $chr <= 239) {
            $result [] = substr ( $str, $i, 3 );
            $i += 3;
        } elseif (240 <= $chr && $chr <= 247) {
            $result [] = substr ( $str, $i, 4 );
            $i += 4;
        } elseif (248 <= $chr && $chr <= 251) {
            $result [] = substr ( $str, $i, 5 );
            $i += 5;
        } elseif (252 <= $chr && $chr <= 253) {
            $result [] = substr ( $str, $i, 6 );
            $i += 6;
        }
    }
    return $result;
}

// 把字符串中的http转成https
function httpTohttps($url){
 return strtr($url,['http://'=>'https://']);
}