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
                }
            };
        })
        .service('ApplicationService', function(
            ApplicationFactory,
            $localStorage,
            DashboardService,
            $rootScope,
            MESSAGES_CONSTANTS,
            toastr
        ) {
            var service = {};

            service.setAppAsDefault = function(app) {
                var updatedApp = {
                    id: app.id,
                    is_default: true
                };

                ApplicationFactory.update(app.id, updatedApp).then(function() {
                    toastr.success('Application Updated');
                });
            };

            service.handleSaveOnSuccess = function(response) {
                //if its my first app i will change the context to this app
                // var noAppsYet =
                //     !$localStorage.applications ||
                //     !$localStorage.applications.length;
                // if (noAppsYet) {
                //     DashboardService.fetchDashboardsByAppId(response.data.id);
                // }
                $rootScope.applications.push(response.data);
                // $scope.$emit('new-application', vm.application);
            };

            return service;
        });
})(window.angular);
