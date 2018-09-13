/**
 * Directive used to change any character to lower case
 */
(function(angular) {
    angular.module('leadwireApp').directive('lowercaseOnly', [
        function() {
            return {
                restrict: 'A',
                require: 'ngModel',
                link: function(scope, element, attrs, ctrl) {

                    element.on('keypress', function(e) {
                        var char = e.char || String.fromCharCode(e.charCode);
                        var keyCode = e.charCode || e.which || e.key;
                        if(keyCode !== 8){
                            if (!/^[a-z0-9._]$/i.test(char) ) {
                                e.preventDefault();
                                return false;
                            }
                        }
                    });

                    function parser(value) {
                        if (ctrl.$isEmpty(value)) {
                            return value;
                        }
                        var formatedValue = value.toLowerCase();
                        if (ctrl.$viewValue !== formatedValue) {
                            ctrl.$setViewValue(formatedValue);
                            ctrl.$render();
                        }
                        return formatedValue;
                    }

                    function formatter(value) {
                        if (ctrl.$isEmpty(value)) {
                            return value;
                        }
                        return value.toLowerCase();
                    }

                    ctrl.$formatters.push(formatter);
                    ctrl.$parsers.push(parser);
                }
            };
        }
    ]);

})(window.angular)