(function(angular) {
    angular
        .module('leadwireApp')
        .factory('ApplicationFactory', function($http, CONFIG) {
            return {
                findAll: function() {
                    return $http.get(CONFIG.BASE_URL + 'api/app/list');
                },
                findMyDashboard: function(id) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/app/' + id + '/dashboards'
                    );
                },
                findInvitedApps: function() {
                    return $http.get(CONFIG.BASE_URL + 'api/app/invited/list');
                },
                save: function(body) {
                    return $http.post(CONFIG.BASE_URL + 'api/app/new', body);
                },
                get: function(id) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/app/' + id + '/get'
                    );
                },
                update: function(id, body) {
                    return $http.put(
                        CONFIG.BASE_URL + 'api/app/' + id + '/update',
                        body
                    );
                },
                remove: function(id) {
                    return $http.delete(
                        CONFIG.BASE_URL + 'api/app/' + id + '/delete'
                    );
                },
                activate: function (id, code) {
                    return $http.post(
                        CONFIG.BASE_URL + 'api/app/' + id + '/activate', {code: code}
                    );
                }
            };
        })
})(window.angular);
