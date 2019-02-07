(function (angular) {
    angular.module('leadwireApp')
        .controller('AddPlanController', [
            'PlanService',
            'toastr',
            'CONFIG',
            'MESSAGES_CONSTANTS',
            '$state',
            AddPlanCtrlFN,
        ]);

    /**
     * Handle add new plan logic
     *
     */
    function AddPlanCtrlFN (
        PlanService,
        toastr,
        CONSTANTS,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.handleOnSubmit = function () {
            PlanService.create(vm.plan)
                .then(vm.handleOnResolve)
                .catch(function () {
                    vm.flipActivityIndicator('isSaving');
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                });
        };

        vm.handleOnResolve = function () {
            vm.flipActivityIndicator('isSaving');
            toastr.success(MESSAGES_CONSTANTS.SUCCESS);
            $state.go('app.management.plans');
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                },
                plan: null,
            });

            // vm.getPlan(appId);
        };

    }
})(window.angular);
