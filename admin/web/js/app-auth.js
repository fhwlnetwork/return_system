function permissionChangeCheck(id){
    //获取当前点击的框值
    var chk = $('#'+id).is(':checked');
    //取消选择，则子类都取消
    if(chk){
        $('.'+id).show();
        $('.'+id+" input[type='checkbox']").prop("checked", 'true');
    }else{
        $('.'+id+" input[type='checkbox']").removeAttr("checked");
        $('.'+id).hide();
    }
}