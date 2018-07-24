angular.module('leadwireApp')
    .factory('Application', function ($http) {
        return {
            findAll: function () {
                return $http.get('/api/app/list');
            }
        };
    });