(function (angular) {
    angular
        .module('leadwireApp')
        .controller('DetailUserController', [
            'UserService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            DetailUserCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function DetailUserCtrlFN (
        UserService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.getUser = function (id) {
            vm.flipActivityIndicator('isLoading');
            UserService.get(id)
                .then(function (user) {
                    vm.flipActivityIndicator('isLoading');
                    vm.user = user;
                })
                .catch(function (err) {
                    vm.flipActivityIndicator('isLoading');
                    // TODO Remove This
                    vm.user = {
                        id: 1,
                        name: 'Ibra',
                        email: 'ibra@gmail.com',
                        active: true,
                        role: ['ROLE_USER', 'ROLE_ADMIN'],
                        plans: [
                            { id: 1, name: 'Premium' },
                            { id: 2, name: 'BASIC' }],
                        applications: [
                            { name: 'App 1 ' },
                            { name: 'App 2' },
                            { name: 'App 3' }],
                    };
                });
        };

        vm.init = function () {
            var userId = $state.params.id;
            if (!!!userId) {
                return $state.go('app.management.users');
            }
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                user: null,
                userId: userId,
            });
            vm.getUser(userId);
        };

    }
})(window.angular);
