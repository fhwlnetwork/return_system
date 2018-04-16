//多选形式
var setting_2 = {
    view: {
        //autoCancelSelected: false,
        //selectedMulti: false,
        showLine: false
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
        onAsyncSuccess: onAsyncSuccess_2,
        onClick:zTreeOnSelect_2
    }
};

var zNodes_2 = null;
//console.log(typeof  currentZTreeId);
//获取ztree对象
var zTree_2;
//异步加载完毕后的回调
var firstAsyncSuccessFlag_2 = 0;
function onAsyncSuccess_2(event, treeId, msg) {
    if (firstAsyncSuccessFlag_2 == 0) {
        try {
            //展开全部节点
            expandNodes_2(zTree_2.getNodes());
            if(typeof currentZTreeId_from != 'undefined' && currentZTreeId_from != ''){
                var zTreeIds = currentZTreeId_from.split(",");
                for (i=0; i<zTreeIds.length; i++)
                {
                    var currentNode = zTree_2.getNodeByParam("id", zTreeIds[i], null);
                    zTree_2.selectNode(currentNode);
                    zTreeOnLoaded_2('', '', currentNode);
                }
            }
            firstAsyncSuccessFlag_2 = 1;
        } catch (err) {

        }
    }
}
//展开全部节点
function expandNodes_2(nodes) {
    if (!nodes) return;
    for (var i=0, l=nodes.length; i<l; i++) {
        zTree_2.expandNode(nodes[i], true, false, false);
        if (nodes[i].isParent && nodes[i].zAsync) {
            expandNodes_2(nodes[i].children);
        }
    }
}
//已经选中的tid
var selectNodes_2 = [];
function zTreeOnLoaded_2(event, treeId, treeNode) {
    //如果点击的treeId已经存在，那么取消选中
    var idIndex = $.inArray(treeNode.id, selectNodes_2);
    if(idIndex > -1){
        selectNodes_2.splice(idIndex, 1);
    }
    //如果不存在，那么选中
    else{
        selectNodes_2.push(treeNode.id);
    }

    //保存已选中的名称
    var selectNameOfNode = [];
    //进行选择操作
    if(selectNodes_2.length>0){
        $.each(selectNodes_2, function(n, id){
            //console.log(n);
            var node = zTree_2.getNodeByParam('id', id, null);
            //将数据保存在名称数组中
            selectNameOfNode.push(node.name);
            if(n==0){
                zTree_2.selectNode(node);
            }else{
                zTree_2.selectNode(node, true);
            }
        });
    }
    //取消选择
    else{
        selectNameOfNode = [];
        zTree_2.cancelSelectedNode(treeNode);
    }
    //数据更新到input
    $("#zTreeId_from").val(selectNodes);
    $("#zTreeSelect_from").html(selectNameOfNode.join('，'));
}
//已经选中的tid
var selectNodes_2 = [];
function zTreeOnSelect_2(event, treeId, treeNode) {
    //如果点击的treeId已经存在，那么取消选中

    var idIndex = $.inArray(treeNode.id, selectNodes_2);
    if(idIndex > -1){
        selectNodes_2.splice(idIndex, 1);
    }
    //如果不存在，那么选中
    else{
        selectNodes_2.push(treeNode.id);
    }

    //保存已选中的名称
    var selectNameOfNode_2 = [];
    //进行选择操作
    if(selectNodes_2.length>0){
        $.each(selectNodes_2, function(n, id){
            //console.log(n);
            var node = zTree_2.getNodeByParam('id', id, null);
            //将数据保存在名称数组中
            selectNameOfNode_2.push(node.name);
            if(n==0){
                zTree_2.selectNode(node);
            }else{
                zTree_2.selectNode(node, true);
            }
        });
    }
    //取消选择
    else{
        selectNameOfNode_2 = [];
        zTree_2.cancelSelectedNode(treeNode);
    }
    //数据更新到input
    $("#zTreeId_from").val(selectNodes_2);
    $("#zTreeSelect_from").html(selectNameOfNode_2.join('，'));
}

function createTree_from(domId) {
    $.fn.zTree.init($("#"+domId), setting_2, zNodes_2);
    zTree_2 = $.fn.zTree.getZTreeObj(domId);
}