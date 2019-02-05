(function (angular) {
    angular
        .module('leadwireApp')
        .controller('PlanListController', [
            'PlanService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
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
        $state,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.deletePlan = function (id) {
            console.log(id);
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
