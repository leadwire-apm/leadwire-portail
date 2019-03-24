(function(angular) {
    angular.module('leadwireApp').factory('InvitationFactory', [
        '$http',
        'CONFIG',
        function($http, CONFIG) {
            return {
                save: function(body) {
                    return $http.post(
                        CONFIG.BASE_URL + 'api/invitation/new',
                        body
                    );
                },
                get: function(id) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/invitation/' + id + '/get'
                    );
                },
                update: function(id, body) {
                    return $http.put(
                        CONFIG.BASE_URL + 'api/invitation/' + id + '/update',
                        body
                    );
                },
                remove: function(id) {
                    return $http.delete(
                        CONFIG.BASE_URL + 'api/invitation/' + id + '/delete'
                    );
                },
                accept: function(id, body) {
                    return $http.post(
                        CONFIG.BASE_URL + 'api/invitation/' + id + '/accept',
                        body
                    )
                }
            };
        }
    ]);
})(window.angular);
