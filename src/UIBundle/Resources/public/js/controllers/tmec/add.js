(function (angular) {
    angular.module('leadwireApp')
        .controller('AddCompagnesController', [
            'ApplicationTypeService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            '$stateParams',
            AddCompagnesCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function AddCompagnesCtrlFN (
        ApplicationTypeService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
        $stateParams,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.saveAppType = function () {
            vm.flipActivityIndicator('isSaving');
            /*ApplicationTypeService.create(vm.applicationType)
                .then(function () {
                    vm.flipActivityIndicator('isSaving');
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.applicationTypes');
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isSaving');
                    toastr.error(error.message || MESSAGES_CONSTANTS.ERROR);

                });*/
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                },
                compagne: {
                    version: '',
                    description: '',
                    startDate: '',
                    endDate: '',
                    applicationId:$stateParams.id
                },
            });
        };
    }
})(window.angular);
