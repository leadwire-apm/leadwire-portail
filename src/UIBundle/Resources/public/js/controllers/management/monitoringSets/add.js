(function (angular) {
    angular.module('leadwireApp')
        .controller('AddMonitoringSetController', [
            'MonitoringSetService',
            'TemplateService',
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
        TemplateService,
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

        vm.loadTemplates = function() {
            TemplateService.list()
            .then(function(templates) {
                vm.templates = templates;
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
                    version: '',
                    dashboardTemplate: {id:null},
                    indexPatternTemplate: {id:null},
                    indexTemplateTemplate: {id:null},
                },
            });
            vm.loadTemplates();
        };

    }
})(window.angular);
