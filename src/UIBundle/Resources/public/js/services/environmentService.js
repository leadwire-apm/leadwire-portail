(function (angular) {
    angular.module('leadwireApp')
        .service('EnvironmentService', function (
            $localStorage,
            EnvironementFactory,
        ) {
            var service = {};

            service.list = function () {
                return EnvironementFactory.getAll()
                    .then(function (response) {
                        $localStorage.envList = response.data;
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.create = function (environment) {
                return EnvironementFactory.add(environment)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.find = function (id) {
                return EnvironementFactory.get(id)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });

            };

            service.findMinimalist = function (id) {
                return EnvironementFactory.getMinimalist(id)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });

            };

            service.findAllMinimalist = function () {
                return EnvironementFactory.getAllMinimalist()
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });

            };

            service.update = function (environment) {
                return EnvironementFactory.update(environment)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.delete = function (id) {
                return EnvironementFactory.delete(id)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.setDefault = function (id) {
                return EnvironementFactory.setDefault(id)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.getDefault = function () {
                return EnvironementFactory.getDefault()
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            return service;
        });
})(window.angular);
