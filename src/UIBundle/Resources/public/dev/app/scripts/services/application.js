angular.module('leadwireApp').
    factory('ApplicationFactory', function($http, CONFIG) {
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
            remove: function(id) {
                return $http.delete(
                    CONFIG.BASE_URL + 'api/app/' + id + '/delete');
            },

        };
    }).
    service('ApplicationService', function(ApplicationFactory) {
        var service = {};

        service.setAppAsDefault = function(app) {
            var updatedApp = {
                id: app.id,
                is_default: true,
            };

            ApplicationFactory.update(app.id, updatedApp).then(function(response) {
                console.log()
            });
        };

        return service
    });