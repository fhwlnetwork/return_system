/**
 * Created by ligang on 2014/11/10.
 */
angular.module("app.pages.logger", []).factory("logger", [function () {
    var logIt;
    return toastr.options = {
        closeButton: !0,
        positionClass: "toast-bottom-right",
        timeOut: "3000"
    }, logIt = function (message, type) {
        return toastr[type](message)
    }, {
        log: function (message) {
            logIt(message, "info")
        }, logWarning: function (message) {
            logIt(message, "warning")
        }, logSuccess: function (message) {
            logIt(message, "success")
        }, logError: function (message) {
            logIt(message, "error")
        }
    }
}]);

angular.module("app.pages.ctrls", []).controller("NotifyCtrl", ["$scope", "logger", function ($scope, logger) {
    return $scope.notify = function (type) {
        switch (type) {
            case "info":
                return logger.log("Heads up! This alert needs your attention, but it's not super important.");
            case "success":
                return logger.logSuccess("Well done! You successfully read this important alert message.");
            case "warning":
                return logger.logWarning("Warning! Best check yo self, you're not looking too good.");
            case "error":
                return logger.logError("Oh snap! Change a few things up and try submitting again.")
        }
    }
}]);

function userFieldsMap(){
    var user_field = $("#user_fields").val();
    $("#user_fields").val('user_name');
    var middle_field = $("#middle_fields").val();
    $("#middle_fields").val('');
    var map = $("#user_fields_map").val();
    $("#user_fields_map").val(map+user_field+','+middle_field+'\n');
}