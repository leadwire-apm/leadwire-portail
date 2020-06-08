(function(angular) {
    angular.module('leadwireApp').factory('InvitationFactory', [
        '$http',
        'CONFIG',
        function($http, CONFIG) {
            return {
                save: function(body, envId) {
                    return $http.post(
                        CONFIG.BASE_URL + `api/invitation/${envId}/new`,
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
                remove: function(id, data) {
                    return $http.post(
                        CONFIG.BASE_URL + 'api/invitation/' + id + '/delete',
                        data
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
