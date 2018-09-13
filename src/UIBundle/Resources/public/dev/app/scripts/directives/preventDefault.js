/**
 * helper to prevent default behavior on click
 */
(function(angular) {
    angular.module('leadwireApp').directive('preventDefault', function() {
        return function(scope, element) {
            $(element).click(function(event) {
                event.preventDefault();
            });
        };
    });
})(window.angular);
