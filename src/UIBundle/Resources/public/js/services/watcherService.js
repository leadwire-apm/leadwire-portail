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

        service.add = function (data) {
            data.delay = String(data.delay);
            return WatcherFactory.add(data);
        }

        service.list = function (appId, envId) {
            return WatcherFactory.list(appId, envId);
        }
    }
})(window.angular);
