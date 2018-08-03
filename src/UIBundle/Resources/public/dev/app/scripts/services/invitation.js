angular.module('leadwireApp').factory('Invitation', function($http, CONFIG) {
    return {
        save: function(body) {
            return $http.post(CONFIG.BASE_URL + 'api/invitation/new', body);
        },
        get: function(id) {
            return $http.get(CONFIG.BASE_URL + 'api/invitation/' + id + '/get');
        },
        update: function(id, body) {
            return $http.put(
                CONFIG.BASE_URL + 'api/invitation/' + id + '/update', body);
        },

    };
});