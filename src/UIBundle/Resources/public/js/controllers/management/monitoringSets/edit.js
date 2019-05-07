(function (angular) {
    angular.module('leadwireApp')
        .controller('EditMonitoringSetController', [
            'MonitoringSetService',
            'TemplateService',
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
        TemplateService,
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
                    vm.monitoringSet.id = id;
                    vm.monitoringSet.name = monitoringSet.name;
                    vm.monitoringSet.qualifier = monitoringSet.qualifier;
                    vm.monitoringSet.version = monitoringSet.version;
                    const dashboardCandidate = monitoringSet.templates.filter(function(element){return element.type=='Dashboards';});
                    const indexPatternCandidate = monitoringSet.templates.filter(function(element){return element.type=='Index-Pattern';});
                    const indexTemplateCandidate = monitoringSet.templates.filter(function(element){return element.type=='Index-Template';});
                    vm.monitoringSet.dashboardTemplate.id = dashboardCandidate.length > 0 ? dashboardCandidate[0].id : null;
                    vm.monitoringSet.indexPatternTemplate.id = indexPatternCandidate.length > 0 ? indexPatternCandidate[0].id : null;
                    vm.monitoringSet.indexTemplateTemplate.id = indexTemplateCandidate.length > 0 ? indexTemplateCandidate[0].id : null;
                });
        };

        vm.editMonitoringSet = function () {
            vm.flipActivityIndicator('isSaving')
            vm.monitoringSet.templates = [
                vm.monitoringSet.dashboardTemplate,
                vm.monitoringSet.indexPatternTemplate,
                vm.monitoringSet.indexTemplateTemplate,
            ];
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
                    isLoading: false,
                },
                monitoringSet: {
                    id: '',
                    name: '',
                    qualifier: '',
                    version: '',
                    dashboardTemplate: {id:null},
                    indexPatternTemplate: {id:null},
                    indexTemplateTemplate: {id:null},
                    templates: []
                },
            });
            vm.loadTemplates();
            vm.loadMonitoringSet($stateParams.id);
        };

    }
})(window.angular);
