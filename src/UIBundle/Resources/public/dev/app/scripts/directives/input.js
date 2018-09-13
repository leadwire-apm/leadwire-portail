(function(angular) {
    // you cant focus inputs on touch screen , this is a bug between bootstrap-ui and ngTouch
    // this is hack to fix this issue https://github.com/angular-ui/bootstrap/issues/2280
    angular
        .module('leadwireApp')
        .directive('input', [
            function() {
                return {
                    restrict: 'E',
                    link: link
                };
            }
        ])
        .directive('textarea', [
            function() {
                return {
                    restrict: 'E',
                    link: link
                };
            }
        ])
        .directive('select', [
            function() {
                return {
                    restrict: 'E',
                    link: link
                };
            }
        ]);

    function link(scope, elem) {
        // bind the events iff this is an input/textarea within a modal
        if (elem.parents('.modal').length) {
            elem.on('touchstart', function(e) {
                elem.focus();
                // e.preventDefault();
                e.stopPropagation();
            });
        }
    }
})(window.angular);
