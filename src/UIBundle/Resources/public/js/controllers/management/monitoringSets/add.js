(function (angular) {
    angular.module('leadwireApp')
        .controller('AddMonitoringSetController', [
            'MonitoringSetService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            AddMonitoringSetCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function AddMonitoringSetCtrlFN (
        MonitoringSetService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.saveAppType = function () {
            vm.flipActivityIndicator('isSaving');
            MonitoringSetService.create(vm.monitoringSet)
                .then(function () {
                    vm.flipActivityIndicator('isSaving');
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.monitoringSets');
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isSaving');
                    toastr.error(error.message || MESSAGES_CONSTANTS.ERROR);

                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                },
                monitoringSet: {
                    name: '',
                    qualifier: '',
                },
            });
        };

    }
})(window.angular);
