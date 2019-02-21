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

        /**
         *
         * @param {"isSaving","isLoading"} key
         */
        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.fetchTemplates = function () {
            vm.flipActivityIndicator('isLoading');
            TemplateService.list()
                .then(function (templates) {
                    vm.flipActivityIndicator('isLoading');
                    vm.templates = templates;
                })
                .catch(function (err) {
                    vm.flipActivityIndicator('isLoading');
                    // todo
                });
        };

        vm.handleOnDelete = function (id) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        vm.submitDelete(id);
                    } else {
                        swal.close();
                    }
                });

        };

        vm.submitDelete = function (id) {
            TemplateService.delete(id)
                .then(function () {
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                })
                .then(vm.loadApplicationTypes)
                .catch(function (error) {
                    toastr.error(error.message || MESSAGES_CONSTANTS.ERROR);
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
