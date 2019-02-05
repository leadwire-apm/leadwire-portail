(function (angular) {
    angular.module('leadwireApp')
        .service('TemplateService', function (
            TemplateFactory,
        ) {
            var service = {};

            service.list = function () {
                return TemplateFactory.findAll()
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.create = function (template) {
            };

            service.find = function (id) {
                return TemplateFactory.get(id)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });

            };

            service.update = function (template) {
            };

            service.delete = function (id) {
            };

            return service;
        });
})(window.angular);
