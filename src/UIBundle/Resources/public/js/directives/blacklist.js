angular.module('leadwireApp').directive('blacklist', function () {
    return {
        require: 'ngModel',
        link: function (scope, elem, attr, ngModel) {
            var blacklist = JSON.parse(attr.blacklist);
            //For DOM -> model validation
            ngModel.$parsers.unshift(function (value) {
                var valid = true;
                if (blacklist.indexOf(value) !== -1) {
                    valid = false;
                    scope.$parent.ctrl.blackWord = value;
                }
                ngModel.$setValidity('blacklist', valid);
                if (valid) scope.$parent.ctrl.blackWord = undefined;

                return valid ? value : undefined;
            });
        }
    };
});
