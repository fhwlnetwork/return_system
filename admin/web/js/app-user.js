// 用户详情
app.controller("batch-excel", ["$scope", function ($scope) {
    $scope.type_value = 1;
    $scope.getDataType = function(){
        var val = $('input:radio[name*="get_data_type"]:checked').val();
        $scope.type_value = val == 1 ? 1 : 2;
    }
}]);
// 用户搜索
app.controller("searchUserCtrl", ["$scope", "$http", function ($scope, $http) {
    var timeout;
    $scope.init = true; // 初始化状态
    $scope.numPerPageOpt = [1, 2, 3, 10, 20, 50, 100];// 每页显示数
    $scope.searchField = {page:1, perPage:$scope.numPerPageOpt[1]};
    $scope.initField = angular.copy($scope.searchField);
    $scope.maxSize=4;
    $scope.$watch('searchField',function(newValue,oldValue){
        if(newValue===oldValue){ return; } // 跳过第一次
        clearTimeout(timeout);
        timeout = setTimeout($scope.searchUser, 500);
    },true);
    // 用户搜索
    $scope.searchUser = function(){
        $scope.init = false;
        $http({
            method:'GET',
            url:'/api/user/base/search',
            params:$scope.searchField
        }).success(function(data){
            $scope.users = data.items;
            $scope.pagination = data._meta;
        }).error(function(data,status){
            console.log('err:'+status);
        });
    };
    $scope.onNumPerPageChange = function () {
        $scope.searchField.page = 1;
    };
    $scope.reset = function(){
        $scope.searchField = $scope.initField;
    }
}]);
// 用户详情
app.controller("userProfileCtrl", ["$scope", "$http", function ($scope, $http) {
    $scope.menuState = {
        show: false
    }
    $scope.toggleMenu = function() {
        $scope.menuState.show = !$scope.menuState.show;

    }
     $scope.chgDisplay = function (pid, type){
       if ( type == '') {
           $scope.amount = 0;
           $scope[pid] = !$scope[pid];
       } else {
           $scope.amount = 1;
           $scope[pid] = !$scope[pid];
       }
    },
         $scope.chg = function(model) {
        if (model == 'today') {
            $scope.isToday = 1;
            $('#today').parent().addClass('active');
            $('#yesterday').parent().removeClass('active');
        } else {
            $scope.isToday = 0;
            $('#yesterday').parent().addClass('active');
            $('#today').parent().removeClass('active');
        }
             if ($scope[model] == true) {
                 $scope[model] = !$scope[model];
             }

             $scope[model] = !$scope[model];
    }
    $scope.chgBatchDisplay = function (test, type){
             if ( type == 'buy') {
                 $scope[test] = !$scope[test];
                 $scope.renew = 0;
             } else if(type == 'batch_stop'){
                 $scope[test] = !$scope[test];
                 $scope.renew = 2;
             }else {
                 $scope[test] = !$scope[test];
                 $scope.renew = 1;
             }
     },
    //请求的url
    $scope.url = '';
    //请求数据时的参数
    $scope.searchField = '';
    //数据保存的路径
    //$scope.saveWhere = '';
    //数据保存
    $scope.showBody = ''; //数据
    $scope.showHead = ''; //头部
    //发起请求数据
    $scope.getLog = function(){
        $scope.init = false;
        $http({
            method:'GET',
            url:$scope.url,
            params:$scope.searchField
        }).success(function(data){
            $scope[$scope.logType] = angular.copy(data);
            if(data){
                $scope.showHead = data.shift();
                $scope.showBody = data;
            }
        }).error(function(data,status){
            console.log('err:'+status);
        });
    };
    $scope.$watch('logType',function(newValue,oldValue){
        if(newValue===oldValue){ return; } // 跳过第一次
        $scope.changeType();
    },true);
    //当类型改变时的方法
    $scope.changeType = function(){
        if($scope.logType == 'operate'){
            return ;
        }
        if($scope[$scope.logType]){
            var data = angular.copy($scope[$scope.logType]);
            $scope.showHead = data.shift();
            $scope.showBody = data;
            //console.log($scope[$scope.logType]);
            return ;
        }
        //认证
        if($scope.logType == 'login'){
            $scope.url = '/log/login/index';
            $scope.searchField = {user_name:$scope.user_name, showType:'ajax'};
        }
        //明细
        else if($scope.logType == 'detail'){
            var myDate = new Date();
            var year = myDate.getFullYear();
            var month = myDate.getMonth()+1;
            if (month<10){
                month = "0"+month;
            }
            var firstDay = year+'-'+month+"-"+"01";
            $scope.url = '/log/detail/index';
            $scope.searchField = {user_name:$scope.user_name, start_add_time:firstDay, showType:'ajax'};
        }
        //充值
        else if($scope.logType == 'pay'){
            $scope.url = '/financial/pay/list';
            $scope.searchField = {user_name:$scope.user_name, showType:'ajax'};
        }
        //转账
        else if($scope.logType == 'transfer'){
            $scope.url = '/financial/transfer/list';
            $scope.searchField = {user_name_from:$scope.user_name, showType:'ajax'};
        }
        //结算
        else if($scope.logType == 'checkout'){
            $scope.url = '/financial/checkout/list';
            $scope.searchField = {user_name:$scope.user_name, showType:'ajax'};
        }
        //产品转移
        else if($scope.logType == 'prochange'){
            $scope.url = '/log/prochange/index';
            $scope.searchField = {user_name:$scope.user_name, showType:'ajax'};
        }
        // CDR
        else if($scope.logType == 'cdr'){
            $scope.url = '/log/c-d-r/get-ten-cdr';
            $scope.searchField = {user_name:$scope.user_name, showType:'ajax'};
        }
        $scope.getLog();
    }
}]);
// 用户添加
app.controller("addUserCtrl", ["$scope", function ($scope) {
    $scope.chgProduct = function (id){
        $('#packagesFor'+id+" input[type='checkbox']").removeAttr("checked");//取消选择，则子类都取消
        var chk = $('#pro'+id).is(':checked');
        if(!chk){
            $scope.newProduct.item['num'+id] = 0;
        }
        $scope.oneProPackTotal.item[id] = 0;
    },
    $scope.chgPackage = function (pid, modelId, amount){
        if($scope.buyPackage.item[modelId]){
            $scope.oneProPackTotal.item[pid] += amount;
        }else{
            $scope.oneProPackTotal.item[pid] -= amount;
        }
    }
}]);

app.controller("userRechargeCtrl", ["$scope", "$http", function ($scope, $http) {
    $scope.menuState = {
        show: false
    }
    $scope.toggleMenu = function() {
        $scope.menuState.show = !$scope.menuState.show;

    }
    $scope.chgDisplay = function (pid, type){
        if ( type == '') {
            $scope.amount = 0;
            $scope[pid] = !$scope[pid];
        } else {
            $scope.amount = 1;
            $scope[pid] = !$scope[pid];
        }
    },
        //请求的url
    $scope.url = '';
    //请求数据时的参数
    $scope.searchField = '';
    //数据保存的路径
    //$scope.saveWhere = '';
    //数据保存
    $scope.userHead = ''; //数据
    $scope.productHead = ''; //数据
    $scope.payHead = ''; //数据
    $scope.checkoutHead = ''; //数据
    $scope.dateHead = ''; //头部
    $scope.transferHead = ''; //头部
    $scope.refundHead = ''; //头部

    $scope.showUserBody = ''; //数据
    $scope.showProductBody = ''; //数据
    $scope.showCheckoutBody = ''; //数据
    $scope.showPayBody = ''; //数据
    $scope.showDateBody = ''; //头部
    $scope.showTransferFromBody = '';
    $scope.showRefundBody = '';
    //发起请求数据
    $scope.getLog = function(){
        $scope.init = false;
        $http({
            method:'GET',
            url:$scope.url,
            params:$scope.searchField
        }).success(function(data){
            if ($scope.logType == 'detail') {
                $scope[$scope.logType] = angular.copy(data);
                if(data){
                    $scope.userHead = data.header.user_msg;
                    $scope.productHead = data.header.product_detail;
                    $scope.checkoutHead = data.header.checkout_detail;
                    $scope.payHead = data.header.pay_detail;
                    $scope.transferHead = data.header.transfer_detail;
                    $scope.refundHead = data.header.refund_detail;
                    $scope.showUserBody = data.data.user_msg;
                    $scope.showProductBody = data.data.product_detail;
                    $scope.showCheckoutBody = data.data.checkout_detail;
                    $scope.showPayBody = data.data.pay_detail;
                    $scope.showTransferFromBody = data.data.transfer_from_detail;
                    $scope.showTransferToBody = data.data.transfer_to_detail;
                    $scope.showRefundBody = data.data.refund_detail;
                }
            } else {
                $half_year_pay_chart = echarts.init(document.getElementById('recently_pay'));
                $half_year_pay_chart.showLoading({
                    text: "图表数据正在努力加载...",
                    effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
                    textStyle: {
                        fontSize: 20
                    }
                });
                $half_year_checkout_chart = echarts.init(document.getElementById('recently_checkout'));
                $half_year_checkout_chart.showLoading({
                    text: "图表数据正在努力加载...",
                    effect: 'whirling',//'spin' | 'bar' | 'ring' | 'whirling' | 'dynamicLine' | 'bubble'
                    textStyle: {
                        fontSize: 20
                    }
                });
                $half_year_tranfer_from_chart = echarts.init(document.getElementById('transfer_from'));
                $half_year_tranfer_to_chart = echarts.init(document.getElementById('transfer_to'));
                $half_year_refund_chart = echarts.init(document.getElementById('refund'));
                option.series = data.series[0];
                option.xAxis[0].data = data.xAxis;
                option.xAxis.boundaryGap = false;
                option.title.text = data.title[0];
                $half_year_pay_chart.hideLoading();
                $half_year_pay_chart.setOption(option);
                option.series = data.series[1];
                option.title.text = data.title[1];
                $half_year_checkout_chart.hideLoading();
                $half_year_checkout_chart.setOption(option);
                option.series = data.series[2];
                option.title.text = data.title[2];
                $half_year_tranfer_from_chart.setOption(option);
                option.series = data.series[3];
                option.title.text = data.title[3];
                $half_year_tranfer_to_chart.setOption(option);
                option.series = data.series[4];
                option.title.text = data.title[4];
                $half_year_refund_chart.setOption(option);
            }

        }).error(function(data,status){
            console.log('err:'+status);
        });
    };
    $scope.$watch('logType',function(newValue,oldValue){
        if(newValue===oldValue){ return; } // 跳过第一次
        $scope.changeType();
    },true);
    //当类型改变时的方法
    $scope.changeType = function(){
        if($scope.logType == 'operate'){
            return ;
        }
        if($scope[$scope.logType]){
            if ($scope.logType == 'detail') {
                $scope.userHead = data.header.user_msg;
                $scope.productHead = data.header.product_detail;
                $scope.checkoutHead = data.header.checkout_detail;
                $scope.payHead = data.header.pay_detail;
                $scope.transferHead = data.header.transfer_detail;
                $scope.refundHead = data.header.refund_detail;
                $scope.showUserBody = data.data.user_msg;
                $scope.showProductBody = data.data.product_detail;
                $scope.showCheckoutBody = data.data.checkout_detail;
                $scope.showPayBody = data.data.pay_detail;
                $scope.showTransferFromBody = data.data.transfer_from_detail;
                $scope.showTransferToBody = data.data.transfer_to_detail;
                $scope.showRefundBody = data.data.refund_detail;
            }

            //console.log($scope[$scope.logType]);
            return ;
        }
        if($scope.logType == 'detail'){
            //获取上上期结算
            $scope.url = '/report/detail/user';
            $scope.searchField = {user_name:$scope.user_name, type:1,showType:'ajax'};
        }
        else if($scope.logType == 'history'){
            $scope.url = '/report/detail/user';
            $scope.searchField = {user_name:$scope.user_name,  showType:'echarts'};
        }
        $scope.getLog();
    }
}]);

function checkGroupBatchRenew(msg1, msg2, msg3, msg4){
    var group_id = $("#zTreeId").val();
    var product_id = $("#product_id").val();
    var renew_num = $("#renew_num").val();
    if(group_id == ''){
        alert(msg1);return false;
    }
    if(product_id == 0){
        alert(msg2);return false;
    }
    if(renew_num<=0){
        alert(msg3);return false;
    }
    if(!confirm(msg4)){
        return false;
    }
    $.post("batch-renew",{'group_id':group_id,'product_id':product_id,'renew_num':renew_num},function(res){
        alert(res.msg);
        window.location.href = window.location.href;
    },'json')
}

// 批量停机保号
function batchStop(obj) {
    var num = $('#num').val();
    var type = $('#type').val();
    var money = $('#money').val();
    var group_id = $("#zTreeId").val();
    var product_id = $("#product_id").val();

    $.post('batch-stop2',{num: num,type: type,money: money,group_id: group_id},function (e) {
        if(e.status === 1){
            toastr.success(e.msg);
            // alert(e.msg);
            // // window.location.href = window.location.href;
            // toastr.success(e.msg);
            $(obj).parent().append(e.msg);
        }else {
            toastr.error(e.msg);
        }
    },'json')

}

function checkGroupBatchOperate(msg1, msg2, action_type){
    var group_id = $("#zTreeId").val();
    if(group_id == ''){
        alert(msg1);return false;
    }
    if(!confirm(msg2)){
        return false;
    }
    $.post(action_type,{'group_id':group_id},function(res){
        alert(res.msg);
        window.location.href = window.location.href;
    },'json')
}

function checkGroupBatchBuy(msg1, msg2, msg3, msg4){
    var group_id = $("#zTreeId").val();
    var product_id = $("#product_id_package").val();
    var ids = '';
    $('input:checkbox[name*="buyPackage[item]"]:checked').each(function()
    {
        if($(this).val() != 0){
            ids += $(this).val()+':';
        }
    });
    if(group_id == ''){
        alert(msg1);return false;
    }
    if(product_id == 0){
        alert(msg2);return false;
    }
    if(ids == ''){
        alert(msg3);return false;
    }
    if(!confirm(msg4)){
        return false;
    }

    $.post("batch-buy",{'group_id':group_id,'product_id':product_id,'package_id':ids},function(res){
        alert(res.msg);
        window.location.href = window.location.href;
    },'json')
}
function changePackage(package_id, package_amount){
    $('input:checkbox[name*="buyPackage"]').each(function()
    {
        if($(this).val() == package_id){
            var total = $("#buyPackageTotal").html();
            if($(this).prop('checked')){
                $("#buyPackageTotal").html(total*1+package_amount*1);
            }else{
                $("#buyPackageTotal").html(total-package_amount);
            }
        }
    });
}
/**
 * 批量导出用户
 */
function batchExport(url)
{
    $.ajax({
        type:"GET",
        url: url,
        dataType:'json',
        success:function (){
            //window.location.reload();//刷新当前页面.
        }
    });
}
option = {
    backgroundColor: 'white',
    title: {
        text: '堆叠区域图'
    },
    tooltip : {
        trigger: 'axis'
    },
    legend: {
        data:['邮件营销','联盟广告','视频广告','直接访问','搜索引擎']
    },
    toolbox: {
        feature: {
            magicType: {show: true, type: ['line', 'bar']},
            restore: {show: true},
            saveAsImage: {},
        }

    },
    dataZoom: [{
        type: 'inside',
        start: 0,
        end: 100
    }, {
        start: 0,
        end: 100,
        handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
        handleSize: '80%',
        handleStyle: {
            color: '#fff',
            shadowBlur: 3,
            shadowColor: 'rgba(0, 0, 0, 0.6)',
            shadowOffsetX: 2,
            shadowOffsetY: 2
        }
    }],
    grid: {
        left: '3%',
        right: '4%',
        bottom: '7%',
        containLabel: true
    },
    xAxis : [
        {
            axisLabel: {
                interval:0,
                rotate: 0
            },
            type : 'category',
            data : ['周一','周二','周三','周四','周五','周六','周日']
        }
    ],
    yAxis : [
        {
            type : 'value'
        }
    ],
    series : [

    ]
};