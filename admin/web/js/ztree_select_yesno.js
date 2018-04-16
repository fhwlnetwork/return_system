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

//编辑用户判断是否有更改用户组权限，如果没有，那么需要弹框提示没有更改权限。
if($('#groupChange').length >0 && $('#groupChange').val() == 0){
    setting.callback.onClick = zTreeOnSelect_jin;
}
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
function zTreeOnSelect_jin(){
    $("#productError").modal({
        show: true
    });
}
//选中时
function zTreeOnSelect(event, treeId, treeNode) {
    var group_id = treeNode.id;
    //如果yes_no不等于no，正常显示
    if (group_id.length >0 && $('#productAll').length >0 && yes_no != 'no') {
        $.ajax({
            url: '/user/group/ajax-get-products-by-group',
            type: 'POST',
            data: {"group_id": group_id},
            success: function (res) {
                if (typeof (res) != 'undefined') {
                    var groups = res.groups;
                    var products = res.products;
                    if (groups && groups.length > 0) {
                        //编辑用户，把用户已订购的产和用户组绑定的产品求并集
                        if($('#groupChange').length >0){
                            if($('#user_products').length > 0){
                                var user_products = $('#user_products').val().split(",");
                                var canShow = mergeArray(groups, user_products);
                            }else {
                                var canShow = groups;
                            }
                        }else{
                            var canShow = groups;
                        }
                        //console.info(user_products);
                        var inOut = intersectArray(canShow,products);
                        var inner = inOut[0];
                        if (inner && inner.length > 0) {
                            $(".product_list_hide_").show();
                            $(".product_list_money_hide_").show();
                            $(".product_list_word_hide_").show();
                            for (var i = 0; i < products .length; i++) {
                                var pro = parseInt(products[i]);
                                if (inner.indexOf(pro) != -1) {
                                    $('#productAll').show();
                                    $('#products_'+pro+'_label').show();
                                    $('#products_'+pro+'_label').css('border', '1px dashed #C7C7C7');
                                    $('#products_'+pro).show();
                                } else {
                                    $('#products_'+pro+'_label').css('border', 'none');
                                    $('#products_'+pro+'_label').hide();
                                    $('#products_'+pro).hide();
                                }
                            }
                        }

                    } else {
                        if ($('#productError').length >0 ) {
                            $("#productError").modal({
                                show: true
                            });
                        }

                        $('#productAll').hide();
                    }

                }
            }
        });
    }

    $("#zTreeSelect").html(treeNode.id+'：'+treeNode.name);
    $("#zTreeId").val(treeNode.id);
}

/* @param {Array} a 集合A
* @param {Array} b 集合B
* @returns {Array} 两个集合的交集
*/
function intersectArray(a, b){
   var interVal = [];
    for (var k in a) {
        var pro = parseInt(a[k])
        if (b.indexOf(parseInt(a[k])) != '-1') {
            interVal.push(pro)
        }
    }
    return [interVal];
};

//求两个数组的并集
function mergeArray(a, b) {
    Array.prototype.unique = function(){
        var a = {};
        for(var i = 0; i < this.length; i++){
            if(typeof a[this[i]] == "undefined")
                a[this[i]] = 1;
        }
        this.length = 0;
        for(var i in a)
            this[this.length] = i;
        return this;
    }
    return a.concat(b).unique();
}
function createTree(domId) {
    $.fn.zTree.init($("#"+domId), setting, zNodes);
    zTree = $.fn.zTree.getZTreeObj(domId);
}