/**
 * Created by ligang on 2014/11/10.
 */
//操作日志
//显示更多
function showMore(){
    $.get(searchUrl+(searchUrl.indexOf("?") > 0 ? '&' : '?' )+'go=more&key='+key+'&last_id='+last_id, function(result){
        var data = $.parseJSON(result);
        $("#showList").append(data.listContent);
        if(data.last_id==0){
            $("#showMoreText").hide();
            $("#noMoreText").show();
        }else{
            last_id = data.last_id;
        }
    });
}
//显示详情
function showDetail(id){
    $("#detail"+id).toggle(500);
}
