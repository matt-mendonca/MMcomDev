;(function($){
    window.NotionAdmin = window.NotionAdmin || {};

    NotionAdmin.ui = function(){
        var windowLoad = function() {
            $('body').removeClass('loading');
        },

        init = function() {
            $(document).foundation();
            $('.exit-off-canvas').click();
            $('.left-off-canvas-menu .top-menu').click(navTopMenuClick).stop().mouseover(navTopMenuClick);
            $('.left-off-canvas-menu .top-menu i:last-child').click(navTopLinkToggleClick).stop().mouseover(navTopLinkToggleClick);
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
        };

        return {
            windowLoad: windowLoad,
            init: init,
            navTopMenuClick: navTopMenuClick,
            navTopLinkToggleClick: navTopLinkToggleClick
        };
    }();

    NotionAdmin.utils = function(){
        willInsert = function() {
            console.log('willInsert');
            NotionAdmin.ui.init();
        },

        didInsert = function() {
            console.log('didInsert');
            $('.wysiwyg').trumbowyg();
        },

        willDestroyElement = function() {
            console.log('willDestroyElement');
        };

        willRerender = function() {
            console.log('willRerender');
        };

        return {
            willInsert: willInsert,
            didInsert: didInsert,
            willDestroyElement: willDestroyElement,
            willRerender: willRerender
        };
    }();

     $(document).ready(function() {
        
    });

    $(window).load(function() {
        NotionAdmin.ui.windowLoad();
    });
    
    $(window).on('ember-dom-will-insert', _.debounce(NotionAdmin.utils.willInsert, 100));
    $(window).on('ember-dom-did-insert', _.debounce(NotionAdmin.utils.didInsert, 100));
    $(window).on('ember-dom-will-destroy-element', _.debounce(NotionAdmin.utils.willDestroyElement, 100));
    $(window).on('ember-dom-will-rerender', _.debounce(NotionAdmin.utils.willRerender, 100));
    
})(jQuery);
