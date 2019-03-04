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

        vm.loadCompagne = function (id) {
            TmecService.find(id)
                .then(function (compagne) {
                    vm.compagne = compagne;
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
                compagne: {
                    version: '',
                    description: '',
                    startDate: '',
                    endDate: '',
                    applicationId: ''
                },
            });
            vm.loadCompagne($stateParams.id);
        };

    }
})(window.angular);
