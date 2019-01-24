(function (angular) {
    angular.module('leadwireApp')
        .controller('EditApplicationTypeController', [
            'ApplicationTypeService',
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
            ApplicationTypeService.get(id)
                .then(function (appType) {
                    vm.applicationType = appType;
                });

        };

        vm.editAppType = function () {
            ApplicationTypeService.update(vm.applicationType)
                .then(function () {
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.applicationTypes');
                })
                .catch(function () {
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
                    agent: '',
                },
            });
            vm.loadApplicationType($stateParams.id);
        };

    }
})(window.angular);
