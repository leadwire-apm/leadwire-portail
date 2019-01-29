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
                    vm.user = {};
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
