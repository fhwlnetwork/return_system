jQuery(document).ready(function () {
    $('.drag').sortable();//拖拽：竖排调用
    $(".drag_inline").sortable();//拖拽：横排调用
    //显示日期
    $(".inputDate").datetimepicker({
        language : language,//在main.php中定义
        format : "yyyy-mm-dd",
        //weekStart : 1, //周从哪天开始
        autoclose: 1,//选择后自动关闭
        todayBtn: true, //显示今天按钮
        todayHighlight: true, //高亮今天
        minView : 'month', //最小显示月视图
        forceParse: false //当选择器关闭的时候，是否强制解析输入框中的值
        //pickerPosition: "bottom-left",
    });
    //显示时间
    $(".inputTime").datetimepicker({
        language : language,//在main.php中定义
        format : "hh:ii",
        startView: 'day',
        autoclose: 1 //选择后自动关闭
        //weekStart : 1, //周从哪天开始
        //todayBtn: true, //显示今天按钮
        //todayHighlight: true, //高亮今天
        //minView : 'month' //最小显示月视图
        //pickerPosition: "bottom-left",
    });
    //显示日期和时间
    $(".inputDateTime").datetimepicker({
        language : language,
        format : "yyyy-mm-dd hh:ii",
        //weekStart : 1,
        autoclose: 1,//选择后自动关闭
        todayBtn: true, //显示今天按钮
        todayHighlight: true, //高亮今天
        minuteStep: 5, //分钟的时间间隔
        forceParse: false
    });
    //显示日期和时间
    $(".inputDateHour").datetimepicker({
        language : language,
        format : "yyyy-mm-dd hh:00",
        //weekStart : 1,
        autoclose: 1,//选择后自动关闭
        todayBtn: true, //显示今天按钮
        todayHighlight: true, //高亮今天
        //minuteStep: 5, //分钟的时间间隔
        forceParse: false,
        minView : 'day' //最小显示月视图
    });
    //显示今天以后的日期
    $(".inputDateAfterToday").datetimepicker({
        language : language,
        format : "yyyy-mm-dd",
        weekStart : 1,
        startDate : new Date(new Date().valueOf() + 1*24*60*60*1000),//从明天开始才可以选择
        autoclose: 1,//选择后自动关闭
        todayBtn: true, //显示今天按钮
        todayHighlight: true, //高亮今天
        //minuteStep: 5, //分钟的时间间隔
        forceParse: false,
        minView : 'month' //最小显示月视图
    });
    //显示周
    $(".inputWeek").datetimepicker({
        language : language,
        format : "yyyy-mm-dd",
        startView: 2,
        minView: 2,
        weekStart : 1,
        autoclose: 1//选择后自动关闭
    });
    //显示年
    $(".inputYear").datetimepicker({
        language : language,
        format : "yyyy",
        startView: 4,
        minView: 4,
        autoclose: 1//选择后自动关闭
    });
    //显示年月
    $(".inputMonth").datetimepicker({
        language : language,
        format : "yyyy-mm",
        startView: 3,
        minView: 3,
        autoclose: 1//选择后自动关闭
    });
});