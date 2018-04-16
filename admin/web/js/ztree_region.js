var setting = {
    view: {
        selectedMulti: false,
        addHoverDom: addHoverDom,
        removeHoverDom: removeHoverDom,
        showLine: false
    },
    check: {
        //enable: true
    },
    // 数据使用JSON数据
    data: {
        simpleData: {
            enable: true,
            idKey: 'id',
            pIdKey: 'pid'
        }
    },
    // 强行异步加载父节点的子节点
    async: {
        enable: true,
        type: 'get',
        dataType: "text",
        autoParam: ['id', 'name', 'pid'],
        url: '/auth/region/ajax'
    },
    edit: {
        enable: true,
        editNameSelectAll: true,
        showRemoveBtn: showRemoveBtn,
        showRenameBtn: showRenameBtn
    },
    callback: {
        onAsyncSuccess: onAsyncSuccess,
        //beforeEditName: beforeEditName,
        beforeRemove: beforeRemove,
        beforeDrop: beforeDrop,
        onRemove: onRemove,
        onRename: onRename,
        onDrop: onDrop,
        onCheck:false
    }
};

var zNodes = null;

//获取ztree对象
var zTree;

function showBtn(){
    $("#save").show();
}
function beforeDrop(treeId, treeNodes, targetNode, moveType){
    return !(targetNode == null || (moveType != "inner" && !targetNode.parentTId));
}
function onDrop(treeId, treeNodes){
    //if(treeId=='')
    //console.log(zTree);
    showBtn();
}
var firstAsyncSuccessFlag = 0;
function onAsyncSuccess_bak(event, treeId, msg) {
    if (firstAsyncSuccessFlag == 0) {
        try {
            //调用默认展开第一个结点
            var selectedNode = zTree.getSelectedNodes();
            var nodes = zTree.getNodes();
            zTree.expandNode(nodes[0], true);

            var childNodes = zTree.transformToArray(nodes[0]);
            zTree.expandNode(childNodes[1], true);
            //zTree.selectNode(childNodes[1]);
            var childNodes1 = zTree.transformToArray(childNodes[1]);
            zTree.checkNode(childNodes1[1], true, true);
            firstAsyncSuccessFlag = 1;
        } catch (err) {

        }
    }
}
function onAsyncSuccess(event, treeId, msg) {
    if (firstAsyncSuccessFlag == 0) {
        try {
            //展开全部节点
            expandNodes(zTree.getNodes());
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
function createTree(domId) {
    $.fn.zTree.init($("#"+domId), setting, zNodes);
    zTree = $.fn.zTree.getZTreeObj(domId);
}

// 实现树形节点高级操作  增删改
var log, className = "dark";
function beforeDrag(treeId, treeNodes) {
    return true;
}
// 编辑节点名称
function beforeEditName(treeId, treeNode) {
    className = (className === "dark" ? "":"dark");
    zTree.selectNode(treeNode);
    return confirm("进入节点 -- " + treeNode.name + " 的编辑状态吗？");
}

// 删除节点
function beforeRemove(treeId, treeNode) {
    className = (className === "dark" ? "" : "dark");
    zTree.selectNode(treeNode);
    return confirm("确认删除 节点 -- " + treeNode.name + " 吗？");
}
function onRemove(e, treeId, treeNode) {
    showBtn();
}
function onRename(e, treeId, treeNode, isCancel) {
    //显示保存按钮
    showBtn();
}

function showRemoveBtn(treeId, treeNode) {
    return treeNode.id!=1;
}

var newCount = 1;
function addHoverDom(treeId, treeNode) {
    var sObj = $("#" + treeNode.tId + "_span");
    if (treeNode.editNameFlag || $("#addBtn_" + treeNode.tId).length > 0) return;
    var addStr = "<span class='button add' id='addBtn_" + treeNode.tId
        + "' title='add node' onfocus='this.blur();'></span>";
    sObj.after(addStr);
    var btn = $("#addBtn_" + treeNode.tId);
    if (btn) btn.bind("click", function () {
        var newNode = zTree.addNodes(treeNode, {id: (100 + newCount), pId: treeNode.id, name: "node" + (newCount++)});
        //console.log(newNode);
        zTree.editName(newNode[0]);
        showBtn();
        return false;
    });
}

function removeHoverDom(treeId, treeNode) {
    $("#addBtn_" + treeNode.tId).unbind().remove();
}

function showRenameBtn(treeId, treeNode) {
    return treeNode.id!=1;
}

//保存数据
var newZTreeData = [];
//保存更改的数据
function saveAll(){
    var zTreeObj = zTree.getNodes();
    newZTreeData = [];
    getArr(zTreeObj);
    //提交服务器
    $.ajax({
        type: "POST",
        url: "/auth/region/node",
        contentType: "application/json; charset=utf-8",
        data: JSON.stringify(newZTreeData),
        dataType: "json",
        success: function (message) {
            location.reload();
        },
        error: function (message) {
        }
    });
}
//从对象中获取数据
function getArr(zTreeObj){
    $.each(zTreeObj, function(n, obj){
        var one = {
            id:obj.id,
            name:obj.name,
            tId:obj.tId,
            parentTId:obj.parentTId
        };
        //判断是否新数据
        one.isNew = 'status' in obj ? 0 : 1;
        newZTreeData.push(one);
        //如果存在子数组，递归
        if('children' in obj && obj.children.length>0){
            getArr(obj.children);
        }
    });
}

$(document).ready(function () {
    createTree("treeStruct");
});