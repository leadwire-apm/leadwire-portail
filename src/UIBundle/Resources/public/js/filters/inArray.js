(function (angular) {
        angular.module('leadwireApp')
            .filter('inArray', function () {
                return function (items, doFilter, key, elements) {
                    if (!doFilter) {
                        return items;
                    }
                    return items.filter(function (item) {
                        return elements.some(function (element) {
                            return item[key].includes(element);
                        });
                    });
                };
            });
    }
)(window.angular);
