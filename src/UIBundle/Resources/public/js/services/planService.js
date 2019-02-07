(function (angular) {
    angular.module('leadwireApp')
        .service('PlanService', function (
            PlanFactory,
        ) {
            var service = {};

            service.list = function () {
                return PlanFactory.findAll()
                    .then(function (response) {
                        return response.data;
                    })
                    .catch((err) => {
                        return [];
                    });
            };

            service.create = function (plan) {
                return PlanFactory.new(plan)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            };



            service.find = function (id) {
                return PlanFactory.get(id)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            };

            service.delete = function (id) {

            };

            service.update = function (plan) {
                return PlanFactory.update(plan)
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
