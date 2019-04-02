(function (angular) {
    angular.module('leadwireApp')
        .controller('EditMonitoringSetController', [
            'MonitoringSetService',
            '$stateParams',
            'MESSAGES_CONSTANTS',
            '$state',
            'toastr',
            EditMonitoringSetControllerCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function EditMonitoringSetControllerCtrlFN (
        MonitoringSetService,
        $stateParams,
        MESSAGES_CONSTANTS,
        $state,
        toastr,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.loadMonitoringSet = function (id) {
            MonitoringSetService.find(id)
                .then(function (monitoringSet) {
                    vm.monitoringSet = monitoringSet;
                });
        };

        vm.editMonitoringSet = function () {
            vm.flipActivityIndicator('isSaving')
            MonitoringSetService.update(vm.monitoringSet)
                .then(function () {
                    vm.flipActivityIndicator('isSaving')
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.monitoringSets');
                })
                .catch(function () {
                    vm.flipActivityIndicator('isSaving')
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                MonitoringSet: {
                    name: '',
                    qualifier: ''
                },
            });
            vm.loadMonitoringSet($stateParams.id);
        };

    }
})(window.angular);
