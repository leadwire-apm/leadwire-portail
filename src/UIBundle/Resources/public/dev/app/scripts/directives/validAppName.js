angular.module('leadwireApp').directive('blacklist', function() {
    return {
        require: 'ngModel',
        link: function(scope, elem, attr, ngModel) {
            var blacklist = attr.blacklist.split(',');
            console.log(blacklist);
            //For DOM -> model validation
            ngModel.$parsers.unshift(function(value) {
                var valid = true;
                blacklist.forEach(function(blackWord) {
                    if (value.indexOf(blackWord) !== -1)
                        valid = false;
                });
                ngModel.$setValidity('blacklist', valid);
                return valid ? value : undefined;
            });

            //For model -> DOM validation
            ngModel.$formatters.unshift(function(value) {
                var valid = true;
                if (value) {
                    blacklist.forEach(function(blackWord) {
                        if (value.indexOf(blackWord) !== -1)
                            valid = false;
                    });
                }
                ngModel.$setValidity('blacklist', valid);
                return value;
            });
        },
    };
});
