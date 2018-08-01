angular.module('leadwireApp').factory('Application', function($http) {
    return {
        findAll: function() {
            return $http.get('http://localhost:9000/api/app/list');
        },
        findMyApps: function() {
            return $http.get('http://localhost:9000/api/app/invited/list');
        },

    };
});