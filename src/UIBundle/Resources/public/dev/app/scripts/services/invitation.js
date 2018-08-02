angular.module('leadwireApp').factory('Invitation', function($http, CONFIG) {
    return {
        save: function(body) {
            return $http.post(CONFIG.BASE_URL + 'api/invitation/new', body);
        },

    };
});