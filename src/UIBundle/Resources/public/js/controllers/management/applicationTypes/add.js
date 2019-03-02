(function (angular) {
    angular.module('leadwireApp')
        .controller('AddApplicationTypeController', [
            'ApplicationTypeService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            '$stateParams',
            AddApplicationTypeCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function AddApplicationTypeCtrlFN (
        ApplicationTypeService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
        $stateParams,
    ) {
        
        var vm = this;

        vm.saveAppType = function () {
            vm.flipActivityIndicator('isSaving');
            ApplicationTypeService.create(vm.applicationType)
                .then(function () {
                    vm.flipActivityIndicator('isSaving');
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.applicationTypes');
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isSaving');
                    toastr.error(error.message || MESSAGES_CONSTANTS.ERROR);

                });
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
                    applicationId:''
                },
            });
        };

        vm.compagne.applicationId = $stateParams.id;


    }
})(window.angular);
