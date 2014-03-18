$.noConflict();
;(function($){
    window.NotionAdmin = window.NotionAdmin || {};

    NotionAdmin.ui = function(){
        var init = function() {
            $('.exit-off-canvas').click();
            $('.left-off-canvas-menu a').click(NotionAdmin.ui.mainMenuLinkClick);
            $('.left-off-canvas-menu .top-menu').click(navTopMenuClick).stop().mouseover(navTopMenuClick);
            $('.left-off-canvas-menu .top-menu i:last-child').click(navTopLinkToggleClick).stop().mouseover(navTopLinkToggleClick);
            $('#form_body').trumbowyg();
            $('#form_time_zone').chosen();
            $('#form_route').blur(sanitizeRouteField);

            setMenuActiveLinks();
        },

        setMenuActiveLinks = function() {
            var path = window.location.pathname;

            path = path.split('/');

            if(path[2] === 'content') {
                $('.content-link .fi-play').click();
                $('.'+path[3]+'-link a').addClass('active');
            } else if(path[2] === 'users') {
                $('.users-link .fi-play').click();
                $('.'+path[3]+'-link a').addClass('active');
            } else if(path[2] === undefined) {
                $('.dashboard-link a').addClass('active');
            } else {
               $('.'+path[2]+'-link a').addClass('active');
            }
        }

        mainMenuLinkClick = function(event) {
            $('.exit-off-canvas').click();
        },

        navTopMenuClick = function(event) {
            if($('.left-off-canvas-menu').hasClass('expanded')) {
                $('.left-off-canvas-menu').attr('class', 'left-off-canvas-menu');
                $('.left-off-canvas-menu .sub-menu,'+
                  '.left-off-canvas-menu .top-menu li').removeClass('active');
            }
        },

        navTopLinkToggleClick = function(event) {
            var sub_menu = $(this).siblings('a').attr('data-sub-menu');

            event.stopPropagation();

            if(!$('.left-off-canvas-menu').hasClass('expanded')) {
                $('.left-off-canvas-menu').addClass('expanded').addClass(sub_menu);
                $('.left-off-canvas-menu .sub-menu.'+sub_menu+','+
                  '.left-off-canvas-menu .top-menu .'+sub_menu+'-link').addClass('active');
            }
        },

        sanitizeRouteField = function(event) {
            var route = $(this).val();
            
            // Lowercase
                route = route.toLowerCase();
            // Replace spaces with dashes
                route = route.split(' ').join('-');
            // Trim whitespace
                route = $.trim(route);
            // Add a / to the front of the route if it isn't there
                if(route.substring(0, 1) !== '/') {
                    route = "/" + route;
                }

            $(this).val(route);
        };

        return {
            init: init
        };
    }();

    $(document).ready(function() {
        NotionAdmin.ui.init();
    });
})(jQuery);