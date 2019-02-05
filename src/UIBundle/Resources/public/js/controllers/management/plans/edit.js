(function (angular) {
    angular.module('leadwireApp')
        .controller('EditPlanController', [
            'PlanService',
            'toastr',
            'CONFIG',
            'MESSAGES_CONSTANTS',
            '$state',
            EditPlanCtrlFN,
        ]);

    /**
     * Handle add new plan logic
     *
     */
    function EditPlanCtrlFN (
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

        vm.getPlan = function (id) {
            vm.flipActivityIndicator('isLoading');
            PlanService.find(id)
                .then(function (app) {
                    if (app === null) {
                        $state.go('app.management.plans');
                    }
                    vm.flipActivityIndicator('isLoading');

                    vm.plan = app;
                })
                .catch(function () {
                    vm.flipActivityIndicator('isLoading');
                    $state.go('app.management.plans');
                });
        };

        vm.handleOnSubmit = function () {
            PlanService.update(vm.plan)
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
            var planId = $state.params.id;
            vm = angular.extend(vm, {
                ui: {
                    isLoading: false,
                    isSaving: false,
                },
                planId: planId,
                plan: null,
            });

            vm.getPlan(planId);
        };

    }
})(window.angular);
