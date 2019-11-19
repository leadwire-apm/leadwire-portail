(function (angular) {
    angular.module('leadwireApp')
        .controller('EditEnvironmentController', [
            'EnvironmentService',
            'MonitoringSetService',
            '$stateParams',
            'MESSAGES_CONSTANTS',
            '$state',
            'toastr',
            EditApplicationTypeControllerCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function EditApplicationTypeControllerCtrlFN (
        ApplicationTypeService,
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

        vm.loadApplicationType = function (id) {
            ApplicationTypeService.find(id)
                .then(function (appType) {
                    selected = [];
                    vm.applicationType = appType;
                    vm.applicationType.monitoringSets
                    .forEach(function(ms) {
                        selected.push(ms.id);
                    });
                    $('.selectpicker').selectpicker('val', selected);
                    $('.selectpicker').selectpicker('refresh');
                });

        };

        vm.loadMonitoringSets = function() {
            MonitoringSetService.listValid()
            .then(function(monitoringSets) {
                vm.availableMonitoringSets = monitoringSets;
                $('.selectpicker').append(vm.availableMonitoringSets.map(function(v,k){return '<option value="' + v.id + '">'+v.name+'</option>'}));
                $('.selectpicker').selectpicker('refresh');
                vm.loadApplicationType($stateParams.id)
            });
        };

        vm.editAppType = function () {
            vm.flipActivityIndicator('isSaving')
            vm.applicationType.monitoringSets = vm.applicationType.monitoringSets.map(function (ms) {return {'id': ms};});
            ApplicationTypeService.update(vm.applicationType)
                .then(function () {
                    vm.flipActivityIndicator('isSaving')
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.applicationTypes');
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
                applicationType: {
                    name: '',
                    description: '',
                    installation: '',
                    monitoringSets:[]
                },
                availableMonitoringSets: []
            });
            vm.loadMonitoringSets();
        };

    }
})(window.angular);
