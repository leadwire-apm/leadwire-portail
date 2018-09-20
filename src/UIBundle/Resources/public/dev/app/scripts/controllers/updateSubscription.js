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
            '$q',
            'toastr',
            'MESSAGES_CONSTANTS',
            'CONFIG',
            updateSubscriptionCtrlFN
        ]);

    function updateSubscriptionCtrlFN(
        $scope,
        UserService,
        PlanFactory,
        $stateParams,
        $rootScope,
        $state,
        $q,
        toastr,
        MESSAGES_CONSTANTS,
        CONSTANTS
    ) {
        var vm = this;
        var ACTIONS = {
            UPGRADE: 'upgrade',
            DOWNGRADE: 'downgrade'
        };
        var YEARLY_BILLING_TEXT = 'Yearly bill total';
        var MONTHLY_BILLING_TEXT = 'Monthly bill total';

        function flipActivityIndicator(activity) {
            vm.ui[activity] = !vm.ui[activity];
        }

        /**
         *
         * @returns {Promise}
         */
        function loadPlans() {
            vm.flipActivityIndicator('isLoading');
            return PlanFactory.findAll().then(function(response) {
                vm.flipActivityIndicator('isLoading');
                vm.plans = response.data;
            });
        }

        function loadInvoices() {
            vm.flipActivityIndicator();
            return UserService.getInvoices()
                .then(function(response) {
                    vm.flipActivityIndicator();
                    if (response.status === 200) {
                        vm.invoices = response.data;
                    }
                })
                .catch(function() {
                    vm.flipActivityIndicator();
                });
        }

        function loadSubscription() {
            return UserService.getSubscription().then(function(response) {
                vm.userSubscription = response.data;
            });
        }

        function init() {
            vm.flipActivityIndicator('isInitializing');
            var plansPromise = vm.loadPlans();
            var subscriptionPromise = vm.loadSubscription();
            var invoicePromise = vm.loadInvoices();
            $q.all([plansPromise, subscriptionPromise, invoicePromise])
                .then(function() {
                    vm.flipActivityIndicator('isInitializing');
                })
                .catch(function() {
                    vm.flipActivityIndicator('isInitializing');
                    vm.ui.error = true;

                    toastr.error(MESSAGES_CONSTANTS.ERROR);
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

        function createSubscription() {
            if (!vm.billingForm.$invalid) {
                vm.flipActivityIndicator('isSaving');
                UserService.subscribe(vm.billingInformation, $rootScope.user.id)
                    .then(function(response) {
                        if (response.status === 200) {
                            UserService.setProfile(true).then(function() {
                                vm.flipActivityIndicator('isSaving');
                                toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                                $state.go('app.billingList');
                            });
                        } else {
                            vm.flipActivityIndicator('isSaving');
                            vm.handleError(response);
                        }
                    })
                    .catch(function(error) {
                        vm.flipActivityIndicator('isSaving');
                        toastr.error(error.message);
                    });
            } else {
                toastr.error('Verify your card information');
            }
        }

        /**
         * BASIC-> * : POST
         * *(-BASIC) -> * : PUT
         */
        function validateBilling() {
            // if he has an old basic account we need to subscribe first before update
            if (!vm.invoices || !vm.invoices.length) {
                vm.createSubscription();
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
            var payload = angular.extend(
                {},
                {
                    plan: vm.billingInformation.plan,
                    billingType: vm.billingInformation.billingType,
                    periodEnd: vm.userSubscription.current_period_end
                }
            );

            UserService.updateSubscription(payload)
                .then(function(response) {
                    vm.flipActivityIndicator('isSaving');
                    if (response.status === 200) {
                        $state.go('app.billingList');
                        toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    } else {
                        vm.handleError(response);
                    }
                })
                .catch(function(error) {
                    vm.flipActivityIndicator('isSaving');
                    toastr.error(error.message);
                });
        }

        function handleError(response) {
            if (response.data.message) {
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

        function updatePrices(newValue) {
            if (vm.selectedPlan && vm.selectedPlan.price) {
                if (newValue === 'monthly') {
                    vm.exclTaxPrice = vm.selectedPlan.price;
                    vm.ui.billText = MONTHLY_BILLING_TEXT;
                } else {
                    vm.exclTaxPrice =
                        vm.selectedPlan.price *
                        (1 - vm.selectedPlan.discount / 100) *
                        12;
                    vm.ui.billText = YEARLY_BILLING_TEXT;
                }
                vm.inclTaxPrice = calculatePriceInclTax(vm.exclTaxPrice);
            }
        }

        function registerWatchers() {
            $scope.$watch(function() {
                return vm.plans;
            }, updateCantUpgrade);
            $scope.$watch(function() {
                return vm.billingInformation.billingType;
            }, updatePrices);
        }

        vm.onLoad = function() {
            //init state
            vm = angular.extend(vm, {
                moment: moment,
                CONSTANTS: CONSTANTS,
                ui: {
                    billText: MONTHLY_BILLING_TEXT,
                    isSaving: false,
                    isLoading: false,
                    isInitializing: false
                },
                billingInformation: {
                    plan: null,
                    card: {
                        name: $rootScope.user.name
                    },
                    billingType: 'monthly'
                },
                orderBy: 'price',
                action: $stateParams.action,
                isUpgrade: $stateParams.action === ACTIONS.UPGRADE,
                isDowngrade: $stateParams.action === ACTIONS.DOWNGRADE,
                cantMakeUpgrade: false
            });
            vm.flipActivityIndicator = flipActivityIndicator;
            vm.loadPlans = loadPlans;
            vm.loadInvoices = loadInvoices;
            vm.loadSubscription = loadSubscription;
            vm.init = init;
            vm.choosePlan = choosePlan;
            vm.validateBilling = validateBilling;
            vm.shouldShowPlan = shouldShowPlan;
            vm.updateSubscription = updateSubscription;
            vm.createSubscription = createSubscription;
            vm.handleError = handleError;

            vm.init();
            registerWatchers();
        };
    }
})(window.angular, window.moment);
