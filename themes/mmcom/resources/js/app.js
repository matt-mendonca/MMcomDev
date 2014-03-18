$.noConflict();
(function($){
    window.AppJS = window.AppJS || {};

    AppJS.main = function() {
        var mobileMenuToggle = function(event) {
            $('body').toggleClass('menu-open');
        },

        mainMenuLinkClick = function(event) {
            $('body').removeClass('menu-open');
        };

        return {
            mobileMenuToggle: mobileMenuToggle,
            mainMenuLinkClick: mainMenuLinkClick
        }
    }();

    $('.menu-toggle').click(AppJS.main.mobileMenuToggle);
    $('.menu a').click(AppJS.main.mainMenuLinkClick);

    $(document).ready(function() {
        $('body').addClass('loaded');
    });
})(jQuery);