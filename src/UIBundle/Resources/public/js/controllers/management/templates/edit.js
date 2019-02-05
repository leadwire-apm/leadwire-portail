(function (angular) {
    angular.module('leadwireApp')
        .controller('EditTemplateController', [
            'TemplateService',
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
    function EditTemplateCtrlFN (
        TemplateService,
        toastr,
        CONSTANTS,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.getTemplate = function (id) {
            vm.flipActivityIndicator('isLoading')
            TemplateService.find(id)
                .then(function (app) {
                    if (app === null) {
                        $state.go('app.management.templates');
                    }
                    vm.flipActivityIndicator('isLoading')
                    vm.template = app;
                })
                .catch(function () {
                    vm.flipActivityIndicator('isLoading')
                    $state.go('app.management.templates');
                });
        };

        vm.handleOnSubmit = function () {
            console.log(vm.template);
        };

        vm.init = function () {
            var templateId = $state.params.id;
            vm = angular.extend(vm, {
                ui: {
                    isLoading: false,
                    editor: {
                        options: {
                            mode: 'code',
                        },
                    },
                },
                templateId: templateId,
                template: null,
            });

            // vm.getTemplate(appId);
        };

    }
})(window.angular);
