var setting = {
    view: {
        selectedMulti: false,
        addHoverDom: addHoverDom,
        removeHoverDom: removeHoverDom
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
        url: '/auth/structure/ajax'
    },
    edit: {
        enable: true,
        editNameSelectAll: true,
        showRemoveBtn: showRemoveBtn,
        showRenameBtn: showRenameBtn
    },
    callback: {
        //beforeDrag: beforeDrag,
        beforeRemove: beforeRemove,
        beforeRename: beforeRename,
        onRemove: onRemove,
        onRename: onRename,
        //onNodeCreated: onNodeCreated,
        onDrag: onDrag
    }
};

var zNodes = null;

// 实现基本 树形 展示
var clearFlag = false;

//获取ztree对象
var zTree;

function showBtn(){
    $("#save").show();
}
function onNodeCreated() {
    console.log(1);
    //showBtn();
}
function onDrag(){
    showBtn();
}
function onCheck(e, treeId, treeNode) {
    count();
    if (clearFlag) {
        clearCheckedOldNodes();
    }
}
function clearCheckedOldNodes() {
    //var zTree = $.fn.zTree.getZTreeObj("treeDemo"),
        nodes = zTree.getChangeCheckedNodes();
    for (var i = 0, l = nodes.length; i < l; i++) {
        nodes[i].checkedOld = nodes[i].checked;
    }
}
function count() {
    //var zTree = $.fn.zTree.getZTreeObj("treeDemo"),
    var checkCount = zTree.getCheckedNodes(true).length,
        nocheckCount = zTree.getCheckedNodes(false).length,
        changeCount = zTree.getChangeCheckedNodes().length;
    $("#checkCount").text(checkCount);
    $("#nocheckCount").text(nocheckCount);
    $("#changeCount").text(changeCount);

}
function createTree() {
    //console.log($.fn);return;
    //var $j = jQuery.noConflict();
    $.fn.zTree.init($("#treeDemo"), setting, zNodes);
    zTree = $.fn.zTree.getZTreeObj("treeDemo");
    //count();
    clearFlag = $("#last").attr("checked");
}

// 实现树形节点高级操作  增删改
var log, className = "dark";
function beforeDrag(treeId, treeNodes) {
    return true;
}

// 编辑节点名称
function beforeEditName(treeId, treeNode) {
    //className = (className === "dark" ? "" : "dark");
    //showLog("[ "+getTime()+" beforeEditName ]&nbsp;&nbsp;&nbsp;&nbsp; " + treeNode.name); //操作日志
    //var zTree = $.fn.zTree.getZTreeObj("treeDemo");
    //zTree.selectNode(treeNode);
    if(confirm("进入节点 -- " + treeNode.name + " 的编辑状态吗？")){
        zTree.selectNode(treeNode);
    }
}

// 删除节点
function beforeRemove(treeId, treeNode) {
    className = (className === "dark" ? "" : "dark");
    //showLog("[ "+getTime()+" beforeRemove ]&nbsp;&nbsp;&nbsp;&nbsp; " + treeNode.name);
    //var zTree = $.fn.zTree.getZTreeObj("treeDemo");
    zTree.selectNode(treeNode);
    return confirm("确认删除 节点 -- " + treeNode.name + " 吗？");
}
function onRemove(e, treeId, treeNode) {
    showBtn();
    showLog("[ " + getTime() + " onRemove ]&nbsp;&nbsp;&nbsp;&nbsp; " + treeNode.name);
}

// 保存修改后的节点数据.
function beforeRename(treeId, treeNode, newName, isCancel) {
    className = (className === "dark" ? "":"dark");
    //showLog((isCancel ? "<span style='color:red'>":"") + "[ "+getTime()+" beforeRename ]&nbsp;&nbsp;&nbsp;&nbsp; " + treeNode.name + (isCancel ? "</span>":""));
    if (newName.length == 0) {
        alert("节点名称不能为空.");
        var zTree = $.fn.zTree.getZTreeObj("treeDemo");
        setTimeout(function(){zTree.editName(treeNode)}, 10);
        return false;
    }
    return true;
}

function onRename(e, treeId, treeNode, isCancel) {
    showLog((isCancel ? "<span style='color:red'>" : "") + "[ " + getTime() + " onRename ]&nbsp;&nbsp;&nbsp;&nbsp; " + treeNode.name + (isCancel ? "</span>" : ""));
    //console.log(zTree.getNodes());
    //显示保存按钮
    showBtn();
}

function showRemoveBtn(treeId, treeNode) {
    //return !treeNode.isFirstNode;
    return treeNode.id!=1;
}

function showLog(str) {
    if (!log) log = $("#log");
    log.append("<li class='" + className + "'>" + str + "</li>");
    if (log.children("li").length > 8) {
        log.get(0).removeChild(log.children("li")[0]);
    }
}

function getTime() {
    var now = new Date(),
        h = now.getHours(),
        m = now.getMinutes(),
        s = now.getSeconds(),
        ms = now.getMilliseconds();
    return (h + ":" + m + ":" + s + " " + ms);
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
        var zTree = $.fn.zTree.getZTreeObj("treeDemo");
        zTree.addNodes(treeNode, {id: (100 + newCount), pId: treeNode.id, name: "new node" + (newCount++)});
        showBtn();
        return false;
    });
}

function removeHoverDom(treeId, treeNode) {
    $("#addBtn_" + treeNode.tId).unbind().remove();
}

function selectAll() {
    //var zTree = $.fn.zTree.getZTreeObj("treeDemo");
    zTree.setting.edit.editNameSelectAll = $("#selectAll").attr("checked");
}


function showRenameBtn(treeId, treeNode) {
    //return !treeNode.isFirstNode;
    //console.log(treeNode);
    return treeNode.id!=1;
}

//保存数据
var newZTreeData = [];
//保存更改的数据
function saveAll(){
    var zTreeObj = zTree.getNodes();
    getArr(zTreeObj);
    //提交服务器
    console.log(newZTreeData);
    //alert(newZTreeData);
    //return ;
    $.ajax({
        type: "POST",
        url: "/auth/structure/node",
        contentType: "application/json; charset=utf-8",
        data: JSON.stringify(newZTreeData),//JSON.stringify(GetJsonData()),
        dataType: "json",
        success: function (message) {
            //console.log(message);
            location.reload();
        },
        error: function (message) {
            //console.log(message);
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
    createTree();
    $("#init").bind("change", createTree);
    $("#last").bind("change", createTree);
    $.fn.zTree.init($("#treeDemo"), setting, zNodes);
    $("#selectAll").bind("click", selectAll);
});