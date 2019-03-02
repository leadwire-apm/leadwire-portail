(function (angular) {
    angular.module('leadwireApp')
        .service('TmecService', function (
            TmecFactory,
        ) {
            var service = {};

            service.list = function () {
                return TmecFactory.findAll()
                    .then(function (response) {
                        return response.data;
                    })
                    .catch((err) => {
                        return [];
                    });
            };

            service.create = function (plan) {
                return TmecFactory.new(plan)
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
                return TmecFactory.update(plan)
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
