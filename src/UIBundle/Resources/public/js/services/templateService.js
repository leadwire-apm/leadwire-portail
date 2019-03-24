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
                return TemplateFactory.new(template)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
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
                return TemplateFactory.update(template)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.delete = function (id) {
                return TemplateFactory.delete(id)
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
