(function (angular) {
    angular.module('leadwireApp')
        .controller('EditTemplateController', [
            'TemplateService',
            'ApplicationTypeFactory',
            'MonitoringSetFactory',
            'toastr',
            'CONFIG',
            'MESSAGES_CONSTANTS',
            '$state',
            EditTemplateCtrlFN,
        ]);

    /**
     * Handle add new template logic
     *
     */
    function EditTemplateCtrlFN(
        TemplateService,
        ApplicationTypeFactory,
        MonitoringSetFactory,
        toastr,
        CONSTANTS,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        /**
         *
         * @param {"isLoading","isSaving"} key
         */
        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.getTemplateTypes = function () {
            TemplateService.getTypes().then(function (types) { vm.types = types; });
        };

        vm.getTemplate = function (id) {
            vm.flipActivityIndicator('isLoading');
            TemplateService.find(id)
                .then(function (content) {
                    if (content === null) {
                        throw new Error();
                    }
                    vm.flipActivityIndicator('isLoading');
                    vm.template.content = content;
                })
                .catch(function () {
                    vm.flipActivityIndicator('isLoading');
                    $state.go('app.management.templates');
                });
        };

        vm.handleOnSubmit = function () {
            vm.flipActivityIndicator('isSaving');
            TemplateService.update(vm.template)
                .then(function () {
                    vm.flipActivityIndicator('isSaving');
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.templates');
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
                    isSaving: false,
                    editor: {
                        options: {
                            mode: 'code',
                        },
                    },
                },
                template: {
                    id: $state.params.id,
                    name: $state.params.name,
                    type: $state.params.type
                },
                applicationTypes: [],
                monitoringSets: [],
                types: [],
            });
            vm.getTemplateTypes();
            ApplicationTypeFactory.findAll()
                .then(function (response) {
                    vm.applicationTypes = response.data;
                });
            MonitoringSetFactory.findAll().then(function (response) {
                vm.monitoringSets = response.data;
            });
            vm.getTemplate(vm.template.id);
        };

    }
})(window.angular);
