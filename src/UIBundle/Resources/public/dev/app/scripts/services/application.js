angular.module('leadwireApp').factory('Application', function($http, CONFIG) {
    return {
        findAll: function() {
            return $http.get(CONFIG.BASE_URL + 'api/app/list');
        },
        findMyApps: function() {
            return $http.get(CONFIG.BASE_URL + 'api/app/invited/list');
        },
        save: function(body) {
            return $http.post(CONFIG.BASE_URL + 'api/app/new', body);
        },
        get: function(id) {
            return $http.get(CONFIG.BASE_URL + 'api/app/' + id + '/get');
        },
        update: function(id, body) {
            return $http.put(CONFIG.BASE_URL + 'api/app/' + id + '/update',
                body);
        },

    };
});