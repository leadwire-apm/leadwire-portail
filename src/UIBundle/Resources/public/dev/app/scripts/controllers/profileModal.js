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
        CountryService
    ) {
        var vm = this;
        onLoad();
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
            vm.discountedPrice = selectedPlan.price;
        };

        vm.validateBilling = function() {
            if (!vm.billingForm.$invalid) {
                UserService.subscribe(vm.billingInformation, vm.user.id)
                    .then(function(response) {
                        console.log(response);
                        $modalInstance.close();
                    })
                    .catch(function(error) {
                        toastr.error(error.message);
                    });
            }
        };

        function onLoad() {
            vm.user = angular.extend({}, $localStorage.user);
            vm.step = {
                number: 1, //TODO CHANGE THIS ONE
                title: 'User Settings'
            };
            vm.billingInformation = {
                card: {},
                billingType: 'monthly'
            };
            vm.showCheckBoxes = true;

            $scope.$watch(
                function() {
                    return vm.billingInformation.billingType;
                },
                function(newValue) {
                    if (vm.selectedPlan && vm.selectedPlan.price) {
                        if (newValue === 'monthly') {
                            vm.discountedPrice = vm.selectedPlan.price;
                        } else {
                            vm.discountedPrice =
                                vm.selectedPlan.price *
                                (1 - vm.selectedPlan.discount / 100);
                        }
                    }
                }
            );

            PlanFactory.findAll().then(function(response) {
                vm.plans = response.data;
            });
            CountryService.loadCountries();
        }
    }
})(window.angular);
