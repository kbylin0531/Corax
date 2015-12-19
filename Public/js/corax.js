/**
 * Created by Administrator on 2015/12/19.
 */
var Corax = function () {
    /**
     * 开关配置
     * @type {{tooltip: boolean, popover: boolean, nanoScroller: boolean, hiddenElements: boolean, bootstrapSwitch: boolean, dateTime: boolean, tags: boolean}}
     */
    var config = {
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

    var toggleSideBar = function(){
        var b = $("#sidebar-collapse")[0];
        var w = $("#cl-wrapper");

        if(w.hasClass("sb-collapsed")){
            $(".fa",b).addClass("fa-angle-left").removeClass("fa-angle-right");
            w.removeClass("sb-collapsed");
        }else{
            $(".fa",b).removeClass("fa-angle-left").addClass("fa-angle-right");
            w.addClass("sb-collapsed");
        }
    };

    return {

        'start': function () {
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


            /**
             * Click to show or hidden sidebar
             */
            $("#sidebar-collapse").click(function(){
                toggleSideBar();
            });


            /**
             * Show menu behavior
             */
            $(".cl-vnavigation li ul").each(function(){
                $(this).parent().addClass("parent");
            });
            $(".cl-vnavigation li ul li.active").each(function(){
                $(this).parent().show().parent().addClass("open");
            });
            $(".cl-vnavigation").delegate(".parent > a","click",function(e){
                $(".cl-vnavigation .parent.open > ul").not($(this).parent().find("ul")).slideUp(300, 'swing',function(){
                    $(this).parent().removeClass("open");
                });

                var ul = $(this).parent().find("ul");
                ul.slideToggle(300, 'swing', function () {
                    var p = $(this).parent();
                    if(p.hasClass("open")){
                        p.removeClass("open");
                    }else{
                        p.addClass("open");
                    }
                    wrapper_nscroller.nanoScroller({ preventPageScrolling: true });
                });
                e.preventDefault();
            });


            if($("#cl-wrapper").hasClass("fixed-menu")){
                var scroll =  cl_wrapper.find(".menu-space");
                scroll.addClass("nano nscroller");

                function update_height(){
                    var button = cl_wrapper.find(".collapse-button");
                    var collapseH = button.outerHeight();
                    var navH = $("#head-nav").height();
                    var height = $(window).height() - ((button.is(":visible"))?collapseH:0) - navH;
                    scroll.css("height",height);
                    wrapper_nscroller.nanoScroller({ preventPageScrolling: true });
                }
                $(window).resize(function() {
                    update_height();
                });

                update_height();
                wrapper_nscroller.nanoScroller({ preventPageScrolling: true });
            }else{
                $(window).resize(function(){
                    //updateHeight();
                });
                //updateHeight();
            }

            if(config.nanoScroller){
                $(".nscroller").nanoScroller();
            }


            if(config.hiddenElements){
                /*Dropdown shown event*/
                $('.dropdown').on('shown.bs.dropdown', function () {
                    $(".nscroller").nanoScroller();
                });
            }
        }

    };

}();