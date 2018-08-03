angular.module('leadwireApp').
    factory('HttpInterceptor', function($q, $location,toastr,MESSAGES_CONSTANTS) {
        return {
            request: function(config) {
                // console.log(config.headers)
                config.headers = config.headers || {};
                return config;
            },
            responseError: function(response) {
                // console.log(response)
                console.log('Error: ', response);
                console.log('status: ', response.status);
                if (response.status === 401) {
                    $location.path('/login');
                } else if (response.status === 500) {
                    toastr.error(MESSAGES_CONSTANTS.ERROR)
                    return MESSAGES_CONSTANTS.ERROR;
                } else if (response.status === 400) {
                    //TODO NEED TO GET MESSAGES
                    return response;
                } else
                    return response || $q.when(response);
            },
        };
    });

