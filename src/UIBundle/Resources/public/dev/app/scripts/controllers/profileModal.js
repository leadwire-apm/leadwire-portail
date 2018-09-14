(function(angular) {
    angular
        .module('leadwireApp')
        .controller('profileModalCtrl', [
            '$localStorage',
            'PlanFactory',
            '$location',
            '$modalInstance',
            'toastr',
            '$scope',
            'UserService',
            'CountryService',
            'MESSAGES_CONSTANTS',
            'CONFIG',
            ProfileModalCtrlFN
        ]);

    function ProfileModalCtrlFN(
        $localStorage,
        PlanFactory,
        $location,
        $modalInstance,
        toastr,
        $scope,
        UserService,
        CountryService,
        MESSAGES_CONSTANTS,
        CONSTANTS
    ) {
        var vm = this;
        var YEARLY_BILLING_TEXT = 'Yearly bill total';
        var MONTHLY_BILLING_TEXT = 'Monthly bill total';
        onLoad();
        vm.flipActivityIndicator = function() {
            vm.ui.isSaving = !vm.ui.isSaving;
        };

        vm.save = function() {
            if (vm.userForm.$valid) {
                UserService.saveUser(vm.user, vm.avatar)
                    .then(function(fileName) {
                        vm.handleSuccessForm(fileName);
                    })
                    .catch(function(error) {
                        toastr.error(error);
                    });
            }
        };
        vm.nextStep = function() {
            vm.step.number++;
            vm.changeTitle();
        };
        vm.previousStep = function() {
            vm.step.number--;
            vm.changeTitle();
        };

        vm.changeTitle = function() {
            switch (vm.step.number) {
                case 1: {
                    vm.step.title = 'User Settings';
                    break;
                }
                case 2: {
                    vm.step.title = 'Pricing Plans';
                    break;
                }
                case 3: {
                    vm.step.title = 'Billing';
                    break;
                }
            }
        };

        vm.handleSuccessForm = function handleSuccess(fileName) {
            $localStorage.user = vm.user;
            toastr.success('User has been updated successfully');
            if (fileName) {
                $scope.$emit('update:image', fileName);
            }
            vm.nextStep();
        };

        vm.choosePlan = function(selectedPlan) {
            vm.billingInformation.plan = selectedPlan.id;
            vm.billingInformation.card.name = vm.user.name;
            vm.selectedPlan = selectedPlan;
            vm.exclTaxPrice = selectedPlan.price;
            vm.inclTaxPrice = calculatePriceInclTax(selectedPlan.price);
        };

        vm.validatePlan = function() {
            if (vm.selectedPlan.price == 0) {
                subscribe();
            } else {
                vm.nextStep();
            }
        };

        /**
         * In the case of first we always show all plans
         * @returns {boolean}
         */
        vm.shouldShowPlan = function() {
            return true;
        };

        vm.validateBilling = function() {
            if (!vm.billingForm.$invalid) {
                subscribe();
            }
        };

        function subscribe() {
            vm.flipActivityIndicator();
            UserService.subscribe(vm.billingInformation)
                .then(function(response) {
                    vm.flipActivityIndicator();
                    if (response.status === 200) {
                        toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                        UserService.setProfile(true);
                        $modalInstance.close();
                    } else {
                        if (response.data.message) {
                            toastr.error(response.data.message);
                        } else {
                            toastr.error(MESSAGES_CONSTANTS.ERROR);
                        }
                    }
                })
                .catch(function(error) {
                    toastr.error(error.message);
                });
        }

        function registerWatchers() {
            $scope.$watch(
                function() {
                    return vm.billingInformation.billingType;
                },
                function(newValue) {
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
                        vm.inclTaxPrice = calculatePriceInclTax(
                            vm.exclTaxPrice
                        );
                    }
                }
            );
        }

        function loadPlans() {
            PlanFactory.findAll().then(function(response) {
                vm.plans = response.data;
            });
        }

        function onLoad() {
            vm = angular.extend(vm, {
                user: angular.extend({}, $localStorage.user),
                step: {
                    number: 1,
                    title: 'User Settings'
                },
                billingInformation: {
                    card: {},
                    billingType: 'monthly'
                },
                ui: {
                    billText: MONTHLY_BILLING_TEXT
                },
                showCheckBoxes: true
            });
            registerWatchers();
            loadPlans();
            CountryService.loadCountries();
        }

        function calculatePriceInclTax(price) {
            var inclPrice = price * (1 + CONSTANTS.TAX / 100);
            return inclPrice.toFixed(2);
        }
    }
})(window.angular);
