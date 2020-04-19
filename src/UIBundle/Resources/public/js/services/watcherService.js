(function (angular) {
    angular.module('leadwireApp')
        .service('WatcherService', [
            'WatcherFactory',
            WatcherServiceFN,
        ]);

    function WatcherServiceFN(
        WatcherFactory,
    ) {

        var service = this;

        service.saveOrUpdate = function (data) {
            data.delay = String(data.delay);
            return WatcherFactory.saveOrUpdate(data);
        }

        service.list = function (appId, envId) {
            return WatcherFactory.list(appId, envId);
        }

        service.delete = function (id) {
            return WatcherFactory.delete(id);
        }
    }
})(window.angular);
