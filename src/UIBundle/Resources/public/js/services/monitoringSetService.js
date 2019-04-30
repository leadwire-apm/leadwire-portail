(function (angular) {
    angular.module('leadwireApp')
        .service('MonitoringSetService', function (
            MonitoringSetFactory,
        ) {
            var service = {};

            service.list = function () {
                return MonitoringSetFactory.findAll()
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.listValid = function () {
                return MonitoringSetFactory.findAllValid()
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.find = function (id) {
                return MonitoringSetFactory.get(id)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.create = function (monitoringSet) {
                return MonitoringSetFactory.new(monitoringSet)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.update = function (monitoringSet) {
                return MonitoringSetFactory.update(monitoringSet)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            };

            service.delete = function (monitoringSetId) {
                return MonitoringSetFactory.remove(monitoringSetId)
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
