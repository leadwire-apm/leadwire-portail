(function(angular) {
    angular
        .module('leadwireApp')
        .controller('profileModalCtrl', [
            '$localStorage',
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
            vm.step.number++;
            vm.changeTitle();
        };
        vm.changeTitle = function() {
            console.log(vm.step.number);
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

        // $modalInstance.close();

        vm.handleSuccessForm = function handleSuccess(fileName) {
            $localStorage.user = vm.user;
            toastr.success('User has been updated successfully');
            if (fileName) {
                $scope.$emit('update:image', fileName);
            }
            vm.nextStep();
        };

        function onLoad() {
            vm.user = angular.extend({}, $localStorage.user);
            vm.step = {
                number: 2, //TODO CHANGE THIS ONE
                title: 'User Settings'
            };
            vm.showCheckBoxes = true;

            CountryService.loadCountries();
        }
    }
})(window.angular);
