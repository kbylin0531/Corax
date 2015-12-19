var App = function () {

  var config = {//Basic Config
    tooltip: true,
    popover: true,
    nanoScroller: true,
    hiddenElements: true,
    bootstrapSwitch:true,
    dateTime:true,
    tags:true
  };
  

  /**
   * 顶部栏一下的区域
   * @type {*|jQuery|HTMLElement}
   */
  var cl_wrapper = $("#cl-wrapper");
  var wrapper_nscroller = cl_wrapper.find(".nscroller");


      function toggleSideBar(_this){
        var b = $("#sidebar-collapse")[0];
        var w = $("#cl-wrapper");
        var s = $(".cl-sidebar");
        
        if(w.hasClass("sb-collapsed")){
          $(".fa",b).addClass("fa-angle-left").removeClass("fa-angle-right");
          w.removeClass("sb-collapsed");
        }else{
          $(".fa",b).removeClass("fa-angle-left").addClass("fa-angle-right");
          w.addClass("sb-collapsed");
        }
        //updateHeight();
      }
      
      function updateHeight(){
        if(!cl_wrapper.hasClass("fixed-menu")){
          var button = $("#cl-wrapper .collapse-button").outerHeight();
          var navH = $("#head-nav").height();
          //var document = $(document).height();
          var cont = $("#pcont").height();
          var sidebar = ($(window).width() > 755 && $(window).width() < 963)?0:$("#cl-wrapper .menu-space .content").height();
          var windowH = $(window).height();
          
          if(sidebar < windowH && cont < windowH){
            if(($(window).width() > 755 && $(window).width() < 963)){
              var height = windowH;
            }else{
              var height = windowH - button - navH;
            }
          }else if((sidebar < cont && sidebar > windowH) || (sidebar < windowH && sidebar < cont)){
            var height = cont + button + navH;
          }else if(sidebar > windowH && sidebar > cont){
            var height = sidebar + button;
          }  
          
          // var height = ($("#pcont").height() < $(window).height())?$(window).height():$(document).height();
          $("#cl-wrapper .menu-space").css("min-height",height);
        }else{
          wrapper_nscroller.nanoScroller({ preventPageScrolling: true });
        }
      }
        
  return {
   
    init: function (options) {
      //Extends basic config with options
      $.extend( config, options );
      

      


      /*SubMenu hover */

        /**
         * Return to top
         * @type {*|jQuery|HTMLElement}
         */
      var button = $('<a href="#" class="back-to-top"><i class="fa fa-angle-up"></i></a>');
      button.appendTo("body");
      jQuery(window).scroll(function() {
        if (jQuery(this).scrollTop() > offset) {
          jQuery('.back-to-top').fadeIn(duration);
        } else {
          jQuery('.back-to-top').fadeOut(duration);
        }
      });

      jQuery('.back-to-top').click(function(event) {
        event.preventDefault();
        jQuery('html, body').animate({scrollTop: 0}, duration);
        return false;
      });

      /*NanoScroller*/

      /*Bind plugins on hidden elements*/

    },

    toggleSideBar: function(){
      toggleSideBar();
    }

  };
 
}();
