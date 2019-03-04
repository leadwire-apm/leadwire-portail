(function (angular) {
    angular.module('leadwireApp')
        .controller('EditCompagnesController', [
            'TmecService',
            '$stateParams',
            'MESSAGES_CONSTANTS',
            '$state',
            'toastr',
            EditCompagnesCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function EditCompagnesCtrlFN (
        TmecService,
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
            TmecService.find(id)
                .then(function (appType) {
                    vm.applicationType = appType;
                });

        };

        vm.editAppType = function () {
            vm.flipActivityIndicator('isSaving')
            TmecService.update(vm.applicationType)
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
