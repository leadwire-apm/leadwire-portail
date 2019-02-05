(function (angular) {
    angular.module('leadwireApp')
        .controller('ListTemplateController', [
            'TemplateService',
            'toastr',
            'CONFIG',
            'MESSAGES_CONSTANTS',
            '$state',
            ListTemplateCtrlFN,
        ]);

    /**
     * Handle add new template logic
     *
     */
    function ListTemplateCtrlFN (
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

        vm.fetchTemplates = function () {
            TemplateService.list()
                .then(function (templates) {
                    vm.templates = templates;
                })
                .catch(function (err) {
                    // todo
                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isLoading: false,
                },
                templates: [],
            });
            vm.fetchTemplates();
        };

    }
})(window.angular);
