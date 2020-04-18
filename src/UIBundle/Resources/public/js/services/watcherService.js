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
            return WatcherFactory.add(data);
        }
    }
})(window.angular);
