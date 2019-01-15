(function (angular) {
    angular
        .module('leadwireApp')
        .service('PlanService', function (
            PlanFactory,
        ) {
            var service = {};

            service.list = function () {
                return PlanFactory.findAll()
                    .then(function (response) {
                        return response.data
                    }).catch((err) => {
                        return []
                    })
            };

            service.delete = function (id) {

            };

            return service;
        });
})(window.angular);
