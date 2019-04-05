(function (angular) {
    angular.module('leadwireApp')
        .controller('AddTemplateController', [
            'TemplateService',
            'ApplicationTypeFactory',
            'toastr',
            'CONFIG',
            'MESSAGES_CONSTANTS',
            '$state',
            AddTemplateCtrlFN,
        ]);

    /**
     * Handle add new template logic
     *
     */
    function AddTemplateCtrlFN (
        TemplateService,
        ApplicationTypeFactory,
        toastr,
        CONSTANTS,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.getTemplateTypes = function() {
            TemplateService.getTypes().then(function(types){vm.types = types;});
        };

        vm.handleOnSubmit = function () {
            vm.flipActivityIndicator('isSaving');
            TemplateService.create(vm.template)
                .then(function () {
                    vm.flipActivityIndicator('isSaving');
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.templates')
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isSaving');
                    toastr.error(error.message || MESSAGES_CONSTANTS.ERROR);
                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isLoading: false,
                    editor: {
                        options: {
                            mode: 'code',
                        },
                    },
                },
                template: {
                    name: null,
                    content: null,
                    version: null,
                    type: null,
                    monitoringSet: {
                        id: null
                    },
                    applicationType: {
                        id: null
                    }
                },
            });
            vm.getTemplateTypes();
            ApplicationTypeFactory.findAll().then(function (response) {
                vm.applicationTypes = response.data;
            });

            MonitoringSetFactory.findAll().then(function (response) {
                vm.monitoringSets = response.data;
            });
        };

    }
})(window.angular);
