(function (angular) {
    angular.module('leadwireApp').
        factory('WatcherFactory', ['$http', 'CONFIG', WatcherFactoryFN]);

    function WatcherFactoryFN($http, CONFIG) {
        return {
            saveOrUpdate: function (data) {
                return $http.post(
                    CONFIG.BASE_URL + 'api/watcher/add',
                    data
                );
            },
            list: function (appId, envId) {
                return $http.post(
                    CONFIG.BASE_URL + 'api/watcher/list',
                    {appId, envId}
                );
            },
            delete: function (id, data) {
                return $http.post(
                    CONFIG.BASE_URL + `api/watcher/${id}/delete`, data
                );
            },
            execute: function (id, data) {
                return $http.post(
                    CONFIG.BASE_URL + `api/watcher/${id}/execute`,
                    data
                );
            },
        };
    }
})(window.angular);
