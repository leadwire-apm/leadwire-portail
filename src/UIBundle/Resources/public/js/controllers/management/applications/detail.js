(function (angular, moment) {
    angular.module('leadwireApp')
        .controller('ManageApplicationsDetailController', [
            'ApplicationService',
            'CodeService',
            'toastr',
            'CONFIG',
            'MESSAGES_CONSTANTS',
            '$state',
            ManageApplicationsDetailCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ManageApplicationsDetailCtrlFN (
        ApplicationService,
        CodeService,
        toastr,
        CONSTANTS,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.loginMethod = CONSTANTS.LEADWIRE_LOGIN_METHOD;
        vm.ownerTitle = "Owner Github :"

        if(vm.loginMethod === 'proxy' || vm.loginMethod === 'login'){
            vm.ownerTitle = "Owner Login Id :"
        }

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.getApplication = function (id) {
            ApplicationService.find(id)
                .then(function (app) {
                    if (app === null) {
                        $state.go('app.management.applications');
                    }
                    vm.application = app;
                })
                .catch(function () {
                    $state.go('app.management.applications');
                });
        };

        vm.loadStats = function (id) {
            ApplicationService.getStats(id)
                .then(function (stats) {
                    vm.applicationStats = stats;
                })
                .catch(function () {
                });
        };

        vm.init = function () {
            var appId = $state.params.id;
            vm = angular.extend(vm, {
                ui: {
                    isLoading: false,
                },
                applicationId: appId,
                applicationStats: [],
                application: null,
                moment: moment,
                CONSTANTS: CONSTANTS,
            });
            if (!!!appId) {
                $state.go('app.management.applications');
                return;
            }

            vm.getApplication(appId);
            vm.loadStats(appId);
        };

    }
})(window.angular, window.moment);
