(function(angular, moment) {
    angular
        .module('leadwireApp')
        .controller('updateSubscriptionCtrl', [
            'UserService',
            'PlanFactory',
            '$stateParams',
            '$rootScope',
            'toastr',
            'MESSAGES_CONSTANTS',
            'CONFIG',
            controller
        ]);

    function controller(
        UserService,
        PlanFactory,
        $stateParams,
        $rootScope,
        toastr,
        MESSAGES_CONSTANTS,
        CONSTANTS
    ) {
        var vm = this;
        var ACTIONS = {
            UPGRADE: 'upgrade',
            DOWNGRADE: 'downgrade'
        };

        function flipActivityIndicator() {
            vm.ui.isLoading = !vm.ui.isLoading;
        }

        function loadPlans() {
            vm.flipActivityIndicator();
            PlanFactory.findAll().then(function(response) {
                vm.flipActivityIndicator();
                vm.plans = response.data;
            });
        }

        function choosePlan(selectedPlan) {
            vm.billingInformation.plan = selectedPlan.id;
            vm.billingInformation.card.name = $rootScope.user.name;
            vm.selectedPlan = selectedPlan;
            if (selectedPlan.price > 0) {
                vm.exclTaxPrice = selectedPlan.price;
                vm.inclTaxPrice = calculatePriceInclTax(selectedPlan.price);
            }
        }

        function validateBilling() {
            if (vm.selectedPlan.price > 0) {
                if (!vm.billingForm.$invalid) {
                    if ($rootScope.user.plan.price === 0) {
                        UserService.subscribe(
                            vm.billingInformation,
                            $rootScope.user.id
                        )
                            .then(function(response) {
                                if (response.status === 200) {
                                    vm.updateSubscription();
                                } else {
                                    handleError(response);
                                }
                            })
                            .catch(function(error) {
                                toastr.error(error.message);
                            });
                    } else {
                        vm.updateSubscription();
                    }
                }
            } else {
                vm.updateSubscription();
            }
        }

        function shouldShowPlan(plan) {
            var currentPlan = $rootScope.user.plan;
            return (
                plan.id !== currentPlan.id &&
                ((vm.isDowngrade && plan.price <= currentPlan.price) ||
                    (vm.isUpgrade && plan.price >= currentPlan.price))
            );
        }

        function updateSubscription() {
            UserService.updateSubscription({
                plan: vm.billingInformation.plan,
                billingType: vm.billingInformation.billingType
            })
                .then(function(response) {
                    if (response.status === 200) {
                        toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    } else {
                        handleError(response);
                    }
                })
                .catch(function(error) {
                    toastr.error(error.message);
                });
        }

        function handleError(response) {
            if (
                response.data.error &&
                response.data.error.exception &&
                response.data.error.exception.length
            ) {
                toastr.error(response.data.error.exception[0].message);
            } else {
                toastr.error(MESSAGES_CONSTANTS.ERROR);
            }
        }
        function calculatePriceInclTax(price) {
            var inclPrice = price * (1 + CONSTANTS.TAX / 100);
            return inclPrice.toFixed(2);
        }

        vm.onLoad = function() {
            vm = angular.extend(vm, {
                moment: moment,
                CONSTANTS: CONSTANTS,
                ui: {},
                billingInformation: {
                    plan: {},
                    card: {
                        name: $rootScope.user.name
                    },
                    billingType: 'monthly'
                },
                action: $stateParams.action,
                isUpgrade: $stateParams.action === ACTIONS.UPGRADE,
                isDowngrade: $stateParams.action === ACTIONS.DOWNGRADE
            });
            vm.flipActivityIndicator = flipActivityIndicator;
            vm.loadPlans = loadPlans;
            vm.choosePlan = choosePlan;
            vm.validateBilling = validateBilling;
            vm.shouldShowPlan = shouldShowPlan;
            vm.updateSubscription = updateSubscription;
            vm.loadPlans();
        };
    }
})(window.angular, window.moment);
