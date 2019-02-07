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
            ApplicationTypeService.find(id)
                .then(function (appType) {
                    vm.applicationType = appType;
                });

        };

        vm.editAppType = function () {
            vm.flipActivityIndicator('isSaving')
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
                    agent: '',
                    installation: '',
                },
            });
            vm.loadApplicationType($stateParams.id);
        };

    }
})(window.angular);
