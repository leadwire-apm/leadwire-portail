(function (angular) {
    angular.module('leadwireApp')
        .controller('AddApplicationTypeController', [
            'ApplicationTypeService',
            'MonitoringSetService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            AddApplicationTypeCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function AddApplicationTypeCtrlFN (
        ApplicationTypeService,
        MonitoringSetService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.loadMonitoringSets = function() {
            MonitoringSetService.listValid()
            .then(function(monitoringSets) {
                vm.availableMonitoringSets = monitoringSets;
                $('.selectpicker').selectpicker('refresh');
            });
        };

        vm.saveAppType = function () {
            vm.flipActivityIndicator('isSaving');
            vm.applicationType.monitoringSets = vm.applicationType.monitoringSets.map(function (msId) {return {'id': msId};});
            ApplicationTypeService.create(vm.applicationType)
                .then(function () {
                    vm.flipActivityIndicator('isSaving');
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.applicationTypes');
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
                applicationType: {
                    name: '',
                    description: '',
                    installation: '',
                    monitoringSets: []
                },
                availableMonitoringSets: [],
            });
            vm.loadMonitoringSets();
        };

    }
})(window.angular);
