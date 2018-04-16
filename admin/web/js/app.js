'use strict';
var app = angular.module("app", ["ui.bootstrap", "app.pages.logger", "app.pages.ctrls"]);

app.factory("logger", [function () {
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
}]).factory('toolService', function(){
    return {
        // 对象转化为数组
        objectToArray:function(obj, key_name, num, addKey){
            if ( addKey === false ) {
                return Object.keys(obj).map(function(key) {
                    return obj[key];
                });
            } else {
                key_name = key_name!=undefined ? key_name : '_item_key';
                return Object.keys(obj).map(function (key) {
                    key = num ? parseInt(key) : key;
                    return Object.defineProperty(obj[key], key_name, {enumerable: false, value: key});
                    //obj[key][key_name] = key;
                    //return obj[key];
                    //return Object.defineProperty(obj[key], '_item_key', {enumerable: false, value: key});
                });
            }
        }
    };
});
app.controller("NavCtrl", ["$scope", function ($scope) {
}]);
app.controller("DashboardCtrl", ["$scope", function ($scope) {
}]);

app.controller("AppCtrl", ["$scope", "$location", function ($scope, $location) {
    return $scope.isSpecificPage = function () {
        return false;
    }, $scope.main = {brand: "Srun4k", name: "Admin"}
}]);
app.directive("toggleMinNav", ["$rootScope", function ($rootScope) {
    return {
        restrict: "A",
        link: function (scope, ele) {
            var $window, Timer, app, updateClass;
            return app = $("#app"), $window = $(window), ele.on("click", function (e) {
                return app.hasClass("nav-min") ? app.removeClass("nav-min") : (app.addClass("nav-min"), $rootScope.$broadcast("minNav:enabled")), e.preventDefault()
            }), Timer = void 0, updateClass = function () {
                var width;
                return width = $window.width(), 768 > width ? app.removeClass("nav-min") : void 0
            }, $window.resize(function () {
                var t;
                return clearTimeout(t), t = setTimeout(updateClass, 300)
            })
        }
    }
}]).directive("collapseNav", [function () {
    return {
        restrict: "A",
        link: function (scope, ele) {
            var $a, $aRest, $lists, $listsRest, app;
            return $lists = ele.find("ul").parent("li"), $lists.append('<i class="fa fa-caret-right icon-has-ul"></i>'), $a = $lists.children("a"), $listsRest = ele.children("li").not($lists), $aRest = $listsRest.children("a"), app = $("#app"), $a.on("click", function (event) {
                var $parent, $this;
                return app.hasClass("nav-min") ? !1 : ($this = $(this), $parent = $this.parent("li"), $lists.not($parent).removeClass("open").find("ul").slideUp(), $parent.toggleClass("open").find("ul").slideToggle(), event.preventDefault())
            }), $aRest.on("click", function () {
                return $lists.removeClass("open").find("ul").slideUp()
            }), scope.$on("minNav:enabled", function () {
                return $lists.removeClass("open").find("ul").slideUp()
            })
        }
    }
}]).directive("slimScroll", [function () {
    return {
        restrict: "A",
        link: function (scope, ele) {
            return ele.slimScroll({height: "100%"})
        }
    }
}]).directive("highlightActive", [function () {
    return {
        restrict: "A",
        controller: ["$scope", "$element", "$attrs", "$location", function ($scope, $element, $attrs, $location) {
            var highlightActive, links, path;
            return links = $element.find("a"), path = function () {
                return $location.path()
            }, highlightActive = function (links, path) {
                return path = "#" + path, angular.forEach(links, function (link) {
                    var $li, $link, href;
                    return $link = angular.element(link), $li = $link.parent("li"), href = $link.attr("href"), $li.hasClass("active") && $li.removeClass("active"), 0 === path.indexOf(href) ? $li.addClass("active") : void 0
                })
            }, highlightActive(links, $location.path()), $scope.$watch(path, function (newVal, oldVal) {
                return newVal !== oldVal ? highlightActive(links, $location.path()) : void 0
            })
        }]
    }
}]).directive("toggleOffCanvas", [function () {
    return {
        restrict: "A", link: function (scope, ele) {
            return ele.on("click", function () {
                return $("#app").toggleClass("on-canvas")
            })
        }
    }
}]).directive("uiRangeSlider", [function () {
    return {
        restrict: "A", link: function (scope, ele) {
            return ele.slider()
        }
    }
}]).directive("uiFileUpload", [function() {
    return {
        restrict: "A",
        link: function(scope, ele) {
            return ele.bootstrapFileInput()
        }
    }
}])
;

app.filter('ObjectToArray', function () {
    return function (obj, key_name, addKey) {
        if ( addKey === false ) {
            return Object.keys(obj).map(function(key) {
                return obj[key];
            });
        } else {
            key_name = key_name!=undefined ? key_name : '_item_key';
            return Object.keys(obj).map(function (key) {
                obj[key][key_name] = key;
                return obj[key];
                //return Object.defineProperty(obj[key], '$key', { enumerable: false, value: key});
            });
        }
    };
})
.directive("focusInput", ["$timeout", function ($timeout) {
    return {
        link: function (scope, ele, attrs) {
            return scope.$watch(attrs.focusInput, function (newVal) {
                return newVal ? $timeout(function () {
                    return ele[0].focus()
                }, 0, !1) : void 0
            })
        }
    }
}])
;
var base64EncodeChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
var base64DecodeChars = new Array(
    -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
    -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
    -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63,
    52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1,
    -1,  0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 10, 11, 12, 13, 14,
    15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1,
    -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
    41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1);
function base64encode(str) {
    var out, i, len;
    var c1, c2, c3;
    len = str.length;
    i = 0;
    out = "";
    while(i < len) {
        c1 = str.charCodeAt(i++) & 0xff;
        if(i == len)
        {
            out += base64EncodeChars.charAt(c1 >> 2);
            out += base64EncodeChars.charAt((c1 & 0x3) << 4);
            out += "==";
            break;
        }
        c2 = str.charCodeAt(i++);
        if(i == len)
        {
            out += base64EncodeChars.charAt(c1 >> 2);
            out += base64EncodeChars.charAt(((c1 & 0x3)<< 4) | ((c2 & 0xF0) >> 4));
            out += base64EncodeChars.charAt((c2 & 0xF) << 2);
            out += "=";
            break;
        }
        c3 = str.charCodeAt(i++);
        out += base64EncodeChars.charAt(c1 >> 2);
        out += base64EncodeChars.charAt(((c1 & 0x3)<< 4) | ((c2 & 0xF0) >> 4));
        out += base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >>6));
        out += base64EncodeChars.charAt(c3 & 0x3F);
    }
    return out;
}
function checkps(){
    var p = $("input[name='LoginForm[password]']").val();
    $("input[name='LoginForm[password]']").val(base64encode(p));
}