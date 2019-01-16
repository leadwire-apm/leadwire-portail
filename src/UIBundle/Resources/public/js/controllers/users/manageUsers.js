(function (angular) {
    angular
        .module('leadwireApp')
        .controller('ManageUsersController', [
            'UserService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            ManageUsersCtrlFN
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ManageUsersCtrlFN(
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


        vm.loadUsers = function () {

            vm.flipActivityIndicator('isLoading');
            UserService.list().then(function (response) {
                vm.flipActivityIndicator('isLoading');
                vm.users = response.data;
            }).catch(function (err) {
                vm.flipActivityIndicator('isLoading');
                console.log('error', err);
                vm.users = [
                    {id: 1, name: 'Ibra', email: 'ibra@gmail.com', active: true},
                    {id: 2, name: 'dali', email: 'dali@gmail.com', active: false},
                    {id: 3, name: 'omar', email: 'omar@gmail.com', active: true},
                ]
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
            vm.loadUsers()
        };

    }
})(window.angular);
