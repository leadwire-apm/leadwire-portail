(function (angular) {
    angular
        .module('leadwireApp')
        .controller('PlanListController', [
            'PlanService',
            'toastr',
            'MESSAGES_CONSTANTS',
            PlanListCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function PlanListCtrlFN (
        PlanService,
        toastr,
        MESSAGES_CONSTANTS,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.deletePlan = function (id) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        PlanService.delete(id)
                        .then(function () {
                            toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                        })
                        .then(vm.loadPlans)
                        .catch(function (error) {
                            toastr.error(error.message || MESSAGES_CONSTANTS.ERROR);
                        });
                    } else {
                        swal.close();
                    }
                });
        };

        vm.loadPlans = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            PlanService.list()
                .then(function (plans) {
                    vm.flipActivityIndicator('isLoading');
                    vm.plans = plans;
                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                plans: [],
            });
            vm.loadPlans();
        };

    }
})(window.angular);
