(function (angular) {
    angular.module('leadwireApp')
        .service('OverviewService', function (OverviewFactory) {

            var service = {};

            service.getClusterInformations = function () {
                return OverviewFactory.getClusterInformations()
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            };

            return service;
        });
})(window.angular);
