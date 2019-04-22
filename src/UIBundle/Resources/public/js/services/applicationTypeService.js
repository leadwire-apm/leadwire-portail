(function (angular) {
    angular.module('leadwireApp')
        .service('ApplicationTypeService', function (
            ApplicationTypeFactory,
        ) {
            var service = {};

            service.list = function () {
                return ApplicationTypeFactory.findAll()
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.find = function (id) {
                return ApplicationTypeFactory.get(id)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.create = function (appType) {
                return ApplicationTypeFactory.new(appType)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.update = function (applicationType) {
                return ApplicationTypeFactory.update(applicationType)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            };

            service.delete = function (appTypeId) {
                return ApplicationTypeFactory.remove(appTypeId)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            };

            service.initWithDefaultTemplates = function(applicationTypeId) {
                return ApplicationTypeFactory.initWithDefaultTemplates(applicationTypeId)
                .then(function (response) {
                    return response.data;
                })
                .catch(function (err) {
                    throw new Error(err);
                });
            }

            return service;
        });
})(window.angular);
