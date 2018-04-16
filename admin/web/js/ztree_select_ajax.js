//单选形式
var setting = {
    view: {
        autoCancelSelected: false,
        selectedMulti: false,
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
            firstAsyncSuccessFlag = 1;
            //currentZTreeId 当前选中的id
            var currentNode = zTree.getNodeByParam("id", currentZTreeId, null);
            zTree.selectNode(currentNode);
            zTreeOnSelect('', '', currentNode);
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