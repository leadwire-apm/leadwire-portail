(function (angular) {
    angular.module('leadwireApp')
        .controller('AddTemplateController', [
            'TemplateService',
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
        toastr,
        CONSTANTS,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.handleOnSubmit = function () {
            console.log(vm.template);
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
                template: null,
            });

        };

    }
})(window.angular);
