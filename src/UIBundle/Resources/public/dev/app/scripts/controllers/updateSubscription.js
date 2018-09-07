(function(angular, moment) {
    angular
        .module('leadwireApp')
        .controller('updateSubscriptionCtrl', [
            '$scope',
            'UserService',
            'PlanFactory',
            '$stateParams',
            '$rootScope',
            '$state',
            'toastr',
            'MESSAGES_CONSTANTS',
            'CONFIG',
            controller
        ]);

    function controller(
        $scope,
        UserService,
        PlanFactory,
        $stateParams,
        $rootScope,
        $state,
        toastr,
        MESSAGES_CONSTANTS,
        CONSTANTS
    ) {
        var vm = this;
        var ACTIONS = {
            UPGRADE: 'upgrade',
            DOWNGRADE: 'downgrade'
        };
        var YEARLY_MONTH_TEXT = 'Yearly bill total';
        var MONTHLY_MONTH_TEXT = 'Monthly bill total';

        function flipActivityIndicator(activity) {
            vm.ui[activity] = !vm.ui[activity];
        }

        function loadPlans() {
            vm.flipActivityIndicator('isLoading');
            PlanFactory.findAll().then(function(response) {
                vm.flipActivityIndicator('isLoading');
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
                    // if he has an old basic account we need to subscribe first before update
                    if ($rootScope.user.plan.price === 0) {
                        vm.flipActivityIndicator('isSaving');
                        UserService.subscribe(
                            vm.billingInformation,
                            $rootScope.user.id
                        )
                            .then(function(response) {
                                vm.flipActivityIndicator('isSaving');
                                if (response.status === 200) {
                                    vm.updateSubscription();
                                } else {
                                    handleError(response);
                                }
                            })
                            .catch(function(error) {
                                vm.flipActivityIndicator('isSaving');
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
            vm.flipActivityIndicator('isSaving');
            UserService.updateSubscription({
                plan: vm.billingInformation.plan,
                billingType: vm.billingInformation.billingType
            })
                .then(function(response) {
                    vm.flipActivityIndicator('isSaving');
                    if (response.status === 200) {
                        $state.go('app.billingList');
                        toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    } else {
                        handleError(response);
                    }
                })
                .catch(function(error) {
                    vm.flipActivityIndicator('isSaving');
                    toastr.error(error.message);
                });
        }

        function handleError(response) {
            if (
                response.data.message
            ) {
                toastr.error(response.data.message);
            } else {
                toastr.error(MESSAGES_CONSTANTS.ERROR);
            }
        }
        function calculatePriceInclTax(price) {
            var inclPrice = price * (1 + CONSTANTS.TAX / 100);
            return inclPrice.toFixed(2);
        }

        function updateCantUpgrade(newVal) {
            var cant = true;
            if (newVal && newVal.length) {
                var currentPlanPrice = $rootScope.user.plan.price;

                newVal.forEach(function(plan) {
                    if (cant && plan.price > currentPlanPrice) {
                        cant = false;
                    }
                });
            }
            vm.cantMakeUpgrade = cant && vm.isUpgrade;
        }

        function registerWatchers() {
            $scope.$watch(
                function() {
                    return vm.plans;
                },
                function(newVal) {
                    updateCantUpgrade(newVal);
                }
            );
            $scope.$watch(
                function() {
                    return vm.billingInformation.billingType;
                },
                function(newValue) {
                    if (vm.selectedPlan && vm.selectedPlan.price) {
                        if (newValue === 'monthly') {
                            vm.exclTaxPrice = vm.selectedPlan.price;
                            vm.ui.billText = MONTHLY_MONTH_TEXT;
                        } else {
                            vm.exclTaxPrice =
                                vm.selectedPlan.price *
                                (1 - vm.selectedPlan.discount / 100) *
                                12;
                            vm.ui.billText = YEARLY_MONTH_TEXT;
                        }
                        vm.inclTaxPrice = calculatePriceInclTax(
                            vm.exclTaxPrice
                        );
                    }
                }
            );
        }

        vm.onLoad = function() {
            vm = angular.extend(vm, {
                moment: moment,
                CONSTANTS: CONSTANTS,
                ui: {
                    billText: MONTHLY_MONTH_TEXT,
                    isSaving: false,
                    isLoading: false
                },
                billingInformation: {
                    plan: null,
                    card: {
                        name: $rootScope.user.name
                    },
                    billingType: 'monthly'
                },
                action: $stateParams.action,
                isUpgrade: $stateParams.action === ACTIONS.UPGRADE,
                isDowngrade: $stateParams.action === ACTIONS.DOWNGRADE,
                cantMakeUpgrade: false
            });
            vm.flipActivityIndicator = flipActivityIndicator;
            vm.loadPlans = loadPlans;
            vm.choosePlan = choosePlan;
            vm.validateBilling = validateBilling;
            vm.shouldShowPlan = shouldShowPlan;
            vm.updateSubscription = updateSubscription;
            vm.loadPlans();
            registerWatchers();
        };
    }
})(window.angular, window.moment);
