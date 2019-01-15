(function (angular) {
    angular
        .module('leadwireApp')
        .controller('ManageAdminsController', [
            'UserService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            ManageAdminsCtrlFN
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ManageAdminsCtrlFN(
        UserService,
        toastr,
        MESSAGES_CONSTANTS,
        $state
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.deleteUser = function (id) {
            vm.flipActivityIndicator('isSaving');
            UserService.delete(id).then(function (response) {
                vm.flipActivityIndicator('isSaving');

            }).catch(function (err) {
                vm.flipActivityIndicator('isSaving');
            })
        };

        vm.enableUser = function (id) {
            vm.flipActivityIndicator('isSaving');
            UserService.enable(id).then(function (response) {
                vm.flipActivityIndicator('isSaving');

            }).catch(function (err) {
                vm.flipActivityIndicator('isSaving');
            })
        };


        vm.loadAdmins = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            UserService.list().then(function (response) {
                vm.flipActivityIndicator('isLoading');
                vm.users = response.data;
            }).catch(function (err) {
                vm.flipActivityIndicator('isLoading');
                console.log('error', err);

            })
        }

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                users: []
            });
            vm.loadAdmins()
        };

    }
})(window.angular);
