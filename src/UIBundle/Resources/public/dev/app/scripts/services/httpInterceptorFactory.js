angular.module('leadwireApp').
    factory('HttpInterceptor',
        function($q, $location, MESSAGES_CONSTANTS, $localStorage) {
            return {
                request: function(config) {
                    // console.log(config.headers)
                    config.headers = config.headers || {};
                    return config;
                },
                responseError: function(response) {
                    // console.log('Error From the interceptor: ', response);
                    console.log('status: ', response.status);
                    if (response.status === 401) {
                        delete $localStorage.user;
                        $location.path('/login');
                    } else if (response.status === 500) {
                        throw new Error(MESSAGES_CONSTANTS.ERROR);
                    } else if (response.status === 400) {
                        //TODO NEED TO GET MESSAGES
                        return response.data;
                    } else throw new Error(MESSAGES_CONSTANTS.ERROR);

                    // return response || $q.when(response);
                },
            };
        });

