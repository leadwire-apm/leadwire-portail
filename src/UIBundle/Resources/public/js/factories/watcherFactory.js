(function (angular) {
    angular.module('leadwireApp').
        factory('WatcherFactory', ['$http', 'CONFIG', WatcherFactoryFN]);

    function WatcherFactoryFN($http, CONFIG) {
        return {
            add: function (data) {
                return $http.post(
                    CONFIG.BASE_URL + 'api/watcher/add',
                    data
                );
            },
        };
    }
})(window.angular);
