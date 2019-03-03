(function (angular) {
    angular.module('leadwireApp')
        .controller('ListCompagnesController', [
            'TmecFactory',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            '$stateParams',
            ListCompagnesCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ListCompagnesCtrlFN (
        TmecFactory,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
        $stateParams,
    ) {
        var vm = this;

        vm.applicationId = $stateParams.id;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.delete = function (id) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        vm.deleteType(id);
                    } else {
                        swal.close();
                    }
                });

        };

        vm.load = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            TmecFactory.list({"applicationId": vm.applicationId})
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
            TmecFactory.delete(id)
                .then(function () {
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
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
                compagnes: [],
            });
            //vm.load();
        };

    }
})(window.angular);
