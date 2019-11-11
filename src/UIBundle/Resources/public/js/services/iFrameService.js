(function (angular) {
    angular.module('leadwireApp')
        .service('iFrameService', function () {
            var service = {};

            service.setDimensions = function (element) {
                $('body').addClass('iframe');
                $('.main-content').addClass('resisable-iframe');
                var height = window.innerHeight - 120;
                var iFrameHeight = height + 'px'
                var iFrameWidth = '100%';
                element.css('width', iFrameWidth);
                element.css('height', iFrameHeight);
            };

            service.resetDimensions = function (element) {
                $('body').removeClass('iframe');
                $('.main-content').removeClass('resisable-iframe');
            };

            return service;
        });

})(window.angular);
