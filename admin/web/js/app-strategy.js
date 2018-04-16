/**
 * Created by ligang on 2014/11/10.
 */
app.controller("billingCtrl", ["$scope", function($scope){
}]);
app.controller("packageController",["$scope", function($scope){
    //$scope.isCollapsed = 1;
    $scope.$watch('billing_mode',function(newValue,oldValue){
        if(newValue===oldValue){ return; } // 跳过第一次
        $scope.initValue();
        //clearTimeout(timeout);
        //timeout = setTimeout($scope.initValue, 500);
    });
    $scope.initValue = function(){
        if($scope.billing_mode == 3 && $scope.minutes == ''){
            $scope.minutes = 0;
        } else if($scope.billing_mode == 4 && $scope.kbytes == ''){
            $scope.kbytes = 0;
        }
    };
}]);
app.controller("directIdCtrl",["$scope", function($scope){
    return $scope.type2 = {dip_start:"", mask:"", file:"", host_name:"", time_start:"", time_end:""},
    $scope.canSubmit = function() {
        //console.log($scope.type2);
        //导入
        if($scope.addType == 1){
            //console.log(111);
            /*if($scope.type2.file!=''){
                return 1;
            }*/
            return 1;
        }
        //自动生成
        else if($scope.addType == 2){
            var ipReg = /^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/;
            if(($scope.type2.dip_start!='' && typeof($scope.type2.dip_start) != 'undefined' && ipReg.test($scope.type2.dip_start))
                && ($scope.type2.mask!='' && typeof($scope.type2.mask) != 'undefined' &&  !isNaN($scope.type2.mask) && $scope.type2.mask>=0 && $scope.type2.mask<=32)){
                //如果是时段模式
                if($scope.flg==3){
                    if($scope.type2.time_start=='' || typeof($scope.type2.time_start) == 'undefined'
                        || $scope.type2.time_end=='' || typeof($scope.type2.time_end) == 'undefined'
                    ){
                        return 0;
                    }
                }
                return 1;
            }
        }
        //域名直通
        else if($scope.addType == 3){
            var hostReg = /^[0-9a-zA-Z]+[0-9a-zA-Z\.-]*\.[a-zA-Z]{2,4}$/;
            if($scope.type2.host_name!='' && typeof($scope.type2.host_name) != 'undefined' && hostReg.test($scope.type2.host_name)){
                return 1;
            }
        }
        return 0;
    }
}]);
app.controller("helpMessage", ["$scope", function($scope){
    $scope.visible_exampleModal_1 = false;
    $scope.visible_exampleModal_2 = false;
    $scope.visible_exampleModal_3 = false;
    $scope.visible_exampleModal_4 = false;
    $scope.visible_exampleModal_5 = false;
    $scope.visible_exampleModal_6 = false;

    $scope.exampleModal_1 = function () {
        $scope.visible_exampleModal_1 = !$scope.visible_exampleModal_1;
        $scope.visible_exampleModal_2 = false;
        $scope.visible_exampleModal_3 = false;
        $scope.visible_exampleModal_4 = false;
        $scope.visible_exampleModal_5 = false;
        $scope.visible_exampleModal_6 = false;
    }
    $scope.exampleModal_2 = function () {
        $scope.visible_exampleModal_2 = !$scope.visible_exampleModal_2;
        $scope.visible_exampleModal_1 = false;
        $scope.visible_exampleModal_3 = false;
        $scope.visible_exampleModal_4 = false;
        $scope.visible_exampleModal_5 = false;
        $scope.visible_exampleModal_6 = false;
    }
    $scope.exampleModal_3 = function () {
        $scope.visible_exampleModal_3 = !$scope.visible_exampleModal_3;
        $scope.visible_exampleModal_1 = false;
        $scope.visible_exampleModal_2 = false;
        $scope.visible_exampleModal_4 = false;
        $scope.visible_exampleModal_5 = false;
        $scope.visible_exampleModal_6 = false;
    }
    $scope.exampleModal_4 = function () {
        $scope.visible_exampleModal_4 = !$scope.visible_exampleModal_4;
        $scope.visible_exampleModal_1 = false;
        $scope.visible_exampleModal_2 = false;
        $scope.visible_exampleModal_3 = false;
        $scope.visible_exampleModal_5 = false;
        $scope.visible_exampleModal_6 = false;
    }
    $scope.exampleModal_6 = function () {
        $scope.visible_exampleModal_6 = !$scope.visible_exampleModal_6;
        $scope.visible_exampleModal_1 = false;
        $scope.visible_exampleModal_2 = false;
        $scope.visible_exampleModal_3 = false;
        $scope.visible_exampleModal_4 = false;
        $scope.visible_exampleModal_5 = false;
    }
}]);

app.controller("helpMessageChangeMode", ["$scope", function($scope){
    $scope.change_mode = false;

    $scope.change_mode_function = function () {
        $scope.change_mode = !$scope.change_mode;
    }
}]);
function converter(){
    $('#converter').toggle();
}
function time_to_cycle_func(time_unit, seconds) {
    if(time_unit == 'minute'){
        return seconds/60;
    }else if(time_unit == 'second'){
        return seconds;
    }else{
        return seconds;
    }
}
function byte_to_cycle_func(byte_unit, bytes) {
    if(byte_unit == 'kb'){
        return bytes/1024;
    }else if(byte_unit == 'mb'){
        return bytes/1024/1024;
    }else if(byte_unit == 'gb'){
        return bytes/1024/1024/1024;
    }else{
        return bytes;
    }
}
function time_converter(){
    var time_from = $('#time_from').val();
    var time_cycle = $('#time_cycle').val();
    var seconds = '';
    var time_to_cycle = $('#time_to_cycle').val();
    if(time_from>0){
        if(time_cycle == 'hour'){
            seconds = time_from*3600;
        }else if(time_cycle == 'minute'){
            seconds = time_from*60;
        }else if(time_cycle == 'day'){
            seconds = time_from*86400;
        }
    }
    var time_to = time_to_cycle_func(time_to_cycle, seconds);
    $('#time_res').val(time_to);
}
function byte_converter(){
    var byte_from = $('#byte_from').val();
    var byte_cycle = $('#byte_cycle').val();
    var bytes = '';
    var byte_to_cycle = $('#byte_to_cycle').val();
    if(byte_from>0){
        if(byte_cycle == 'GB'){
            bytes = byte_from*1024*1024*1024;
        }else if(byte_cycle == 'MB'){
            bytes = byte_from*1024*1024;
        }else if(byte_cycle == 'KB'){
            bytes = byte_from*1024;
        }
    }
    var byte_to = byte_to_cycle_func(byte_to_cycle, bytes);
    $('#byte_res').val(byte_to);
}
function selectCheckoutMode(basic_cycle, product_id, msg){
    var cycle = $("#checkout_cycle").val();
    $("#checkout_mode").html('');
    if(msg != 'add' && basic_cycle != cycle){
        $.post('/strategy/product/get-used-num',{product_id:product_id},function(used_num){
            if(used_num > 0){
                alert(msg);
            }
        })
    }
    $.post('/strategy/product/ajaxcheckmode',{checkout_cycle:cycle},function(res){
        $("#checkout_mode").append(res);
    })
}
function validateRenew(pid, msg1, msg2){
    if($("#renew_"+pid).val()<=0){
        alert(msg1);return false;
    }
    if(confirm(msg2)){
        return true;
    }else{
        return false;
    }
}
app.controller("batchOperateCtrl", ["$scope", function ($scope) {
    $scope.chgPackage = function (pid, modelId, amount){
        if($scope.buyPackage.item[modelId]){
            $scope.oneProPackTotal.item[pid] += amount;
        }else{
            $scope.oneProPackTotal.item[pid] -= amount;
        }
        $('#total_'+pid).html($scope.oneProPackTotal.item[pid]);
    }
}])