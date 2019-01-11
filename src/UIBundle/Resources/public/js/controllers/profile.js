(function(angular) {
    angular
        .module('leadwireApp')
        .controller('profileCtrl', [
            '$localStorage',
            '$location',
            'toastr',
            '$scope',
            'UserService',
            'CountryService',
            ProfileCtrlFN
        ]);

    function ProfileCtrlFN(
        $localStorage,
        $location,
        toastr,
        $scope,
        UserService,
        CountryService
    ) {
        var vm = this;
        onLoad();

        vm.save = function() {
            if (vm.userForm.$valid) {
                if (!vm.user.defaultApp || !vm.user.defaultApp.id) {
                    vm.user.defaultApp = null;
                }
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
            // $localStorage.user = vm.user;
            if (fileName) {
                $scope.$emit('update:image', fileName);
            }
            toastr.success('User has been updated successfully');
            $location.path('/');
        };

        function onLoad() {
            vm.user = angular.extend({}, $localStorage.user);
            vm.showCheckBoxes = false;
            CountryService.loadCountries();
        }
    }
})(window.angular);
