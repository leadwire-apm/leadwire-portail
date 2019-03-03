(function (angular) {
    angular.module('leadwireApp')
        .service('TmecService', function (
            TmecFactory,
        ) {
            var service = {};

            service.list = function (application) {
                return TmecFactory.getAllByApplicationId(application)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch((err) => {
                        return [];
                    });
            };

            service.create = function (tmec) {
                return TmecFactory.new(tmec)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            };

            service.update = function (tmec) {
                return TmecFactory.update(tmec)
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
