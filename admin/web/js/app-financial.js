/**
 * Created by ligang on 2014/11/10.
 */
app.controller("payCtrl", ["$scope", function ($scope) {
    return $scope.chgPackage = function (modelId, amount){
        if($scope.buyPackage.item[modelId]){
            $scope.packageTotal += amount;
        }else{
            $scope.packageTotal -= amount;
        }
    },
        $scope.chgPackageTotal = function (modelId, amount, num){
            if($scope.buyPackage.item[modelId]){
                if (num == '1') {
                    if ($scope.buyPackage[modelId] > amount) {
                        $scope.packageTotal -= $scope.buyPackage[modelId];
                        $scope.buyPackage[modelId] = amount;
                        $scope.packageTotal += amount;
                    } else if ($scope.buyPackage[modelId] == amount) {
                        if ($scope.buyPackage[modelId] <= 0) {
                            $scope.buyPackage[modelId] = amount;
                            $scope.packageTotal += amount;
                        }
                    } else {
                        $scope.packageTotal += amount;
                        $scope.buyPackage[modelId] = amount;
                    }
                } else {
                    if (num != '') {
                        $scope.packageTotal -= $scope.buyPackage[modelId];
                        $scope.buyPackage[modelId] = amount*num;
                        $scope.packageTotal += $scope.buyPackage[modelId];
                    }
                }
            }else{
                if (num == '1') {
                    $scope.packageTotal -= amount;
                    $scope.buyPackage[modelId] -= amount;
                } else {
                    $scope.packageTotal -= amount*num;
                    $scope.buyPackage[modelId] -= amount*num;
                }
                if ($scope.packageTotal < 0) {
                    $scope.packageTotal = 0;
                }

            }
        },

        $scope.show_input = function(product_id) {
            //$('#pro_label' + product_id).toggle(100);
            if(!$scope.newProduct.item['id'+product_id]){
                $scope.newProduct.item['num'+product_id] = 0;
            }
        },
        $scope.selectType = function(amount){
            $('#totalMsg').html('');
            if($scope.typeValue == '4'){
                $scope.balanceValue = amount;
            }else{
                $scope.balanceValue = 0;
            }
        }
}]);

//如果选择‘缴费到电子钱包’，缴费方式就不显示电子钱包
function selectType(){
    var isShowWallet = $("input[name='PayList[balance][open]']").prop('checked') ? false : true;
    $('#totalMsg').html('');
    $.ajax({
        type:'POST',
        url:"ajax-get-paytypes",
        data:{'isShowWallet':isShowWallet},
        dataType:'json',
        success:function(res){
            var payType = $("#payType");
            payType.empty();
            for(var i=0;i<res.length;i++){
                var option = $('<option>').text(res[i]['name']).val(res[i]['id']);
                if(res[i]['default'] == 1){
                    option.attr('selected', 'selected');
                }
                payType.append(option);
            }
        }
    })
}

$(function () {
    if (typeof is_pro_empty != 'undefined' && is_pro_empty == 0) {
        $('.pro_empty').hide();
    }
    if (typeof is_package_empty != 'undefined' && is_package_empty == 0) {
        $('.package_empty').hide();
    }
});

function checkout(msg1, msg2){
    if($("input[name='checkout_all']").prop('checked') == false && $(":checked[name*='checkout']").size()<1){
        alert(msg1);
        return false;
    }else{
        if(confirm(msg2)){
            return true;
        }else{
            return false;
        }
    }
}
function get_date(type){
    var now = new Date(); //当前日期
    var year = now.getFullYear();
    var input_date = '';
    switch (type) {
        //本日
        case 1:
            input_date = formatDate(now);
            break;
        //本周
        case 2:
            now.setDate(now.getDate() - (now.getDay() - 1));
            input_date = formatDate(now);
            break;
        //本月
        case 3:
            var month = now.getMonth() + 1;
            if (month < 10) {
                month = "0" + month;
            }
            input_date = year + '-' + month + '-01';
            break;
        //本季度
        case 4:
            var month = now.getMonth() + 1;
            month = get_month(month);
            input_date = year + '-' + month + '-01';
            break;
        //本年
        case 5:
            input_date = year + '-01-01';
            break;
    }
    $("#statis_start_time").val(input_date);
    $("#statis_end_time").val('');
    return false;
}
function get_month(month){
    if (month <= 3) {
        return '01';
    }

    if (month <= 6) {
        return '04';
    }

    if (month <= 9) {
        return '07';
    }
    if (month < 13) {
        return '10';
    }
}

function formatDate(date){
    var myyear = date.getFullYear();
    var mymonth = date.getMonth() + 1;
    var myweekday = date.getDate();

    if (mymonth < 10) {
        mymonth = "0" + mymonth;
    }
    if (myweekday < 10) {
        myweekday = "0" + myweekday;
    }
    return (myyear + "-" + mymonth + "-" + myweekday);
}

app.controller("report-financial", ["$scope", function ($scope) {
    $scope.cycle_value = $('#statistical_cycle').val();
    $scope.getInputDate = function(){
        var val = $('#statistical_cycle').val();
        $scope.cycle_value = val;
    }
}]);

function editCheckoutDate(id){
    if(id){
        $('#date_'+id).hide();
        $('#edit_'+id).show();
    }
}
function saveCheckoutDate(id,user_name,product_name,checkout_date){
    if(id){
        var date = $('#new_date_'+id).val();
        if(date){
            $.post('/financial/manualcheckout/ajaxedit',{id:id,user_name:user_name,product_name:product_name,checkout_date:checkout_date,new_date:date},function(res){
                if(res == 'error'){
                    alert('结算日期至少是明天');
                }else if(res>0){
                    $('#edit_'+id).hide();
                    $('#date'+id).html(date);
                    $('#date_'+id).show();
                }else{
                    alert('操作失败');
                }
            });
        }
    }
}
function finReportExcel(url){
    window.location.href = url;
}
function financePrint(){
    $('#finPriTable').attr('border', 1);
    $('.print_hidden').hide();
    $('.print_show').show();

    var bdhtml = window.document.body.innerHTML;
    var startstr = '<!--startprint-->';
    var endstr = '<!--endprint-->';
    var prnhtml = bdhtml.substr(bdhtml.indexOf(startstr)+17);
    prnhtml = prnhtml.substring(0,prnhtml.indexOf(endstr));
    window.document.body.innerHTML = prnhtml;
    window.print();
    window.location.reload();
}