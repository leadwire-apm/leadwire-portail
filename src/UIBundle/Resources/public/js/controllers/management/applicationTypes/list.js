(function (angular) {
    angular.module('leadwireApp')
        .controller('ListApplicationTypesController', [
            'ApplicationTypeService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            ListApplicationTypesCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ListApplicationTypesCtrlFN (
        ApplicationTypeService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.handleOnDelete = function (id) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        vm.deleteType(id);
                    } else {
                        swal.close();
                    }
                });

        };

        vm.loadApplicationTypes = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            ApplicationTypeService.list()
                .then(function (appTypes) {
                    vm.flipActivityIndicator('isLoading');
                    vm.appTypes = appTypes;
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');

                });
        };

        vm.updateType = function (id) {

        };

        vm.deleteType = function (id) {
            ApplicationTypeService.delete(id)
                .then(function () {
                    toastr.success(
                        MESSAGES_CONSTANTS.SUCCESS);
                })
                .then(vm.loadApplicationTypes)
                .catch(function () {
                    toastr.success(MESSAGES_CONSTANTS.ERROR);
                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                appTypes: [],
            });
            vm.loadApplicationTypes();
        };

    }
})(window.angular);
