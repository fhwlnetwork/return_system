//多选形式
var setting = {
    view: {
        autoCancelSelected: false,
        selectedMulti: true,
        showLine: true
    },
    // 数据使用JSON数据
    data: {
        simpleData: {
            enable: true,
            idKey: 'id',
            pIdKey: 'pid'
        }
    },
    check: {
        //enable: true
    },
    // 强行异步加载父节点的子节点
    async: {
        enable: true,
        type: 'get',
        dataType: "text",
        autoParam: ['id', 'name', 'pid'],
        url: '/auth/structure/ajax'
    },
    callback: {
        onAsyncSuccess: onAsyncSuccess,
        onClick:zTreeOnSelect
    }
};
var zNodes = null;
//获取ztree对象
var zTree;
//异步加载完毕后的回调
var firstAsyncSuccessFlag = 0;

function onAsyncSuccess(event, treeId, msg) {
    if (firstAsyncSuccessFlag == 0) {
        try {
            //展开全部节点
            expandNodes(zTree.getNodes());
            //如果有定义当前结点，那么选中这些结点
            if(typeof currentZTreeId != 'undefined' && currentZTreeId != ''){
                var zTreeIds = currentZTreeId.split(",");
                for (i=0; i<zTreeIds.length; i++)
                {
                    var currentNode = zTree.getNodeByParam("id", zTreeIds[i], null);
                    zTree.selectNode(currentNode);
                    zTreeOnLoaded('', '', currentNode);
                }
            }
            firstAsyncSuccessFlag = 1;
        } catch (err) {

        }
    }
}

//展开全部节点
function expandNodes(nodes) {
    if (!nodes) return;
    for (var i=0, l=nodes.length; i<l; i++) {
        zTree.expandNode(nodes[i], true, false, false);
        if (nodes[i].isParent && nodes[i].zAsync) {
            expandNodes(nodes[i].children);
        }
    }
}

if($(":checkbox[name^='UserModel[mgr_product]']").length>0){
    var canMgrProducts=[];
    var id;
    var u = document.location.search.substring(1);
    if(u!="")//解析URL
    {
        var arr1 = u.split("&");
        for (var i = 0; i < arr1.length; i++) {
            if (arr1[i] == "")
                continue;
            var arr2 = arr1[i].split("=");
            if (arr2[0] == "id") {
                id = arr2[1];
            }
        }
    }
    $.ajax({
        url:'/auth/assign/ajax-mgr-product-by-id',
        type:'POST',
        data:{'id':id},
        dataType:'json',
        async:false,
        success:function(res){
            canMgrProducts = res;
        }
    })
}else{
    var canMgrProducts = [];
}

//已经选中的tid
var selectNodes = [];
function zTreeOnLoaded(event, treeId, treeNode) {
    //如果点击的treeId已经存在，那么取消选中
    var idIndex = $.inArray(treeNode.id, selectNodes);
    if(idIndex > -1){
        selectNodes.splice(idIndex, 1);
    }
    //如果不存在，那么选中
    else{
        selectNodes.push(treeNode.id);
    }

    //保存已选中的名称
    var selectNameOfNode = [];
    //进行选择操作
    if(selectNodes.length>0){
        $.each(selectNodes, function(n, id){
            //console.log(n);
            var node = zTree.getNodeByParam('id', id, null);
            //将数据保存在名称数组中
            selectNameOfNode.push(node.name);
            if(n==0){
                zTree.selectNode(node);
            }else{
                zTree.selectNode(node, true);
            }
        });
    }
    //取消选择
    else{
        selectNameOfNode = [];
        zTree.cancelSelectedNode(treeNode);
    }
    //数据更新到input
    $("#zTreeId").val(selectNodes);
    $("#zTreeSelect").html(selectNameOfNode.join('，'));
}

function createTree(domId) {
    $.fn.zTree.init($("#"+domId), setting, zNodes);
    zTree = $.fn.zTree.getZTreeObj(domId);
}

function zTreeOnSelect(event, treeId, treeNode) {
    //如果点击的treeId已经存在，那么取消选中
    // alert(treeNode.id);
    var idIndex = $.inArray(treeNode.id, selectNodes);
    if(idIndex > -1){
        selectNodes.splice(idIndex, 1);
        // 取消选中则移除
        $("#group_" + treeNode.id).remove();
        // 业务逻辑
        if($("#tab_create_cart_by_auto").attr('class') == 'active'){
            $("#auto_user_groups").val(selectNodes);
            $("#excel_user_groups").val(selectNodes);
        }else {
            $("#auto_user_groups").val(selectNodes);
            $("#excel_user_groups").val(selectNodes);
        }
    }
    //如果不存在，那么选中
    else{
        selectNodes.push(treeNode.id);
        // 业务逻辑
        if($("#tab_create_cart_by_auto").attr('class') == 'active'){
            $("#auto_user_groups").val(selectNodes);
            $("#excel_user_groups").val(selectNodes);
        }else {
            $("#auto_user_groups").val(selectNodes);
            $("#excel_user_groups").val(selectNodes);
        }
    }

    //保存已选中的名称
    var selectNameOfNode = [];
    //进行选择操作
    if(selectNodes.length>0){
        $.each(selectNodes, function(n, id){
            //console.log(n);
            var node = zTree.getNodeByParam('id', id, null);
            //将数据保存在名称数组中
            selectNameOfNode.push(node.name);
            if(n==0){
                zTree.selectNode(node);
            }else{
                zTree.selectNode(node, true);
            }
        });
    }
    //取消选择
    else{
        selectNameOfNode = [];
        zTree.cancelSelectedNode(treeNode);
    }
    //数据更新到input
    $("#zTreeId").val(selectNodes);
    $("#zTreeSelect").html(selectNameOfNode.join('，'));
}