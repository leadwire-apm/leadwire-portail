angular.module('leadwireApp').
    factory('ApplicationTypeFactory', function($http, CONFIG) {
        return {
            findAll: function() {
                return $http.get(CONFIG.BASE_URL + 'api/applicationType/list');
            },
        };
    });