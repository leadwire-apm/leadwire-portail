(function (angular) {
    angular.module('leadwireApp')
        .service('CodeService', function (
            CodeFactory,
        ) {
            var service = {};

            service.create = function (appType) {
                return CodeFactory.new(appType)
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
