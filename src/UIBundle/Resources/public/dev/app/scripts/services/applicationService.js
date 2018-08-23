(function(angular) {
    angular
    .module('leadwireApp')
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
            if (response.data !== false) {
                $rootScope.applications.push(response.data);
                toastr.success(MESSAGES_CONSTANTS.ADD_APP_SUCCESS);
            } else {
                toastr.error(MESSAGES_CONSTANTS.ADD_APP_FAILURE ||
                    MESSAGES_CONSTANTS.ERROR);
            }
            //console.log(response.data);

            // $scope.$emit('new-application', vm.application);
        };

        return service;
    });
})(window.angular);
