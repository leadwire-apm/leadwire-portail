(function (angular) {
        angular.module('leadwireApp')
            .filter('inArray', function () {
                return function (items, onlyAdmins, key, element) {
                    if (!onlyAdmins) {
                        return items;
                    }
                    return items.filter(function (item) {
                        return item[key].indexOf(element) !== -1;
                    });
                };
            });
    }
)(window.angular);
