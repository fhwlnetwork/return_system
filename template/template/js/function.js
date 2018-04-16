$(document).ready(function () {
  //回到顶部

  $(window).scroll(function () {
      var sc = $(window).scrollTop();
      if (sc > 100) {
          $(".top").css("display", "block");
      } else {
          $(".top").css("display", "none");
      }
  });
  $("#back-to-top").click(function () {
      $("body,html").animate({
          scrollTop: 0
      }, 1000);
      return false
  });

  $("#wechat").jBox("Tooltip", {
      getContent: "qrcode",
  });

  $(".post-img img").hover(function () {
      $(this).addClass("animated swing")
  }, function () {
      $(this).removeClass("animated swing")
  });

  new jBox('Tooltip', {
    attach: '.jtooltip'
  });
});
