angular.module('leadwireApp').directive('requireiftrue', function($compile) {
    return {
        restrict: 'A',
        require: '?ngModel',
        link: function(scope, el, attrs, ngModel) {
            if (!ngModel) {
                return;
            }
            if (attrs.requireiftrue === 'true') {
                console.log('should require');
                el.attr('required', true);
                // el.removeAttr('requireiftrue');
                $compile(el[0])(scope);
            }
            else {
                console.log('should not require');
            }
        },
    };
});
