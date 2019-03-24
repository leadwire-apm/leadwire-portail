(function (angular) {
    angular.module('leadwireApp')
        .factory('HttpInterceptor', [
            '$q',
            '$location',
            'MESSAGES_CONSTANTS',
            '$localStorage',
            function ($q, $location, MESSAGES_CONSTANTS, $localStorage) {
                return {
                    request: function (config) {
                        if (
                            config.noheaders &&
                            config.headers &&
                            config.headers.Authorization
                        ) {
                            delete config.headers.Authorization;
                        }
                        config.headers = config.headers || {};
                        return config;
                    },
                    responseError: function (response) {
                        console.log('status: ', response.status);
                        if (response.status === 403 || response.status ===
                            401) {
                            $localStorage.$reset();
                            $location.path('/login');
                            throw {
                                data: response.data,
                                error: new Error(response.data.message ||
                                    MESSAGES_CONSTANTS.ERROR),
                            };
                        } else if (response.status === 500) {
                            throw new Error(response.data.message ||
                                MESSAGES_CONSTANTS.ERROR);
                        } else if (response.status === 400) {
                            //TODO NEED TO GET MESSAGES
                            return response;
                        } else {
                            // return response || $q.when(response);
                            throw new Error(response.data.message ||
                                MESSAGES_CONSTANTS.ERROR);
                        }
                    },
                };
            },
        ]);

})(window.angular);
