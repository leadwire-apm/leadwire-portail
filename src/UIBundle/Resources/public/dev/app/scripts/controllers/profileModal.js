(function(angular) {
    angular
        .module('leadwireApp')
        .controller('profileModalCtrl', ProfileModalCtrl);

    function ProfileModalCtrl(
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

        vm.handleSuccessForm = function handleSuccess(fileName) {
            $localStorage.user = vm.user;
            toastr.success('User has been updated successfully');
            if (fileName) {
                $scope.$emit('update-image', fileName);
            }
            $modalInstance.close();
        };

        function onLoad() {
            vm.user = angular.extend({}, $localStorage.user);
            vm.showCheckBoxes = true;

            CountryService.loadCountries();
        }
    }
})(window.angular);
