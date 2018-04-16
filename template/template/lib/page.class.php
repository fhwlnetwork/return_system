<?php
//showPage(页号，总页数，分隔符)
function showPage($page,$totalPage,$sep=" "){
    $url = $_SERVER ['PHP_SELF'];           //获取当前路径
    //$url=$_REQUEST['r'];
    $index = ($page == 1) ? "首页" : "<a href='{$url}?page=1'>首页</a>";
    $last = ($page == $totalPage) ? "尾页" : "<a href='{$url}?page={$totalPage}'>尾页</a>";
    $prevPage=($page>=1)?$page-1:1;
    $nextPage=($page>=$totalPage)?$totalPage:$page+1;
    $prev = ($page == 1) ? "上一页" : "<a href='{$url}?page={$prevPage}'>上一页</a>";
    $next = ($page == $totalPage) ? "下一页" : "<a href='{$url}?page={$nextPage}'>下一页</a>";
    $str = "总共{$totalPage}页/当前是第{$page}页";
    $p="";
    for($i = 1; $i <= $totalPage; $i ++) {
        //当前页无连接
        if ($page == $i) {
            $p .= "[{$i}]";
        } else {
            $p .= "<a href='{$url}?page={$i}'>[{$i}]</a>";
        }
    }
    $pageStr=$str.$sep . $index .$sep. $prev.$sep . $p.$sep . $next.$sep . $last;
    return $pageStr;    //返回分页字符串



}
?>







