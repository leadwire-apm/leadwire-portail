(function (angular) {
    angular.module('leadwireApp').
        factory('WatcherFactory', ['$http', 'CONFIG', WatcherFactoryFN]);

    function WatcherFactoryFN($http, CONFIG) {
        return {
            add: function (data) {
                return $http.put(
                    CONFIG.BASE_URL + 'api/watcher/update',
                    data
                );
            },
        };
    }
})(window.angular);
