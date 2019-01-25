(function (angular) {
    angular.module('leadwireApp')
        .controller('ManageAdminsController', [
            'UserService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            ManageAdminsCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ManageAdminsCtrlFN (
        UserService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;
        var ROLE_ADMIN = 'ROLE_ADMIN';

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.deleteUser = function (id) {
            vm.flipActivityIndicator('isSaving');
            UserService.delete(id)
                .then(function (response) {
                    vm.flipActivityIndicator('isSaving');

                })
                .catch(function (err) {
                    vm.flipActivityIndicator('isSaving');
                });
        };

        vm.loadAdmins = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            UserService.list()
                .then(function (admins) {
                    vm.flipActivityIndicator('isLoading');
                    vm.admins = admins;
                })
                .catch(function (err) {
                    vm.flipActivityIndicator('isLoading');
                    vm.admins = [];
                });
        };

        vm.isAdmin = function (admin) {
            return (admin && admin.roles && admin.roles.indexOf(ROLE_ADMIN) !==
                -1);
        };

        vm.handleChangePermission = function (admin) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        vm.changePermission(admin);
                    } else {
                        swal.close();
                    }
                });
        };

        vm.changePermission = function (admin) {
            vm.flipActivityIndicator('isSaving' + admin.id);
            const user = angular.extend({}, admin);
            if (vm.isAdmin(user)) {
                user.roles = user.roles.filter(function (role) {
                    return role !== ROLE_ADMIN;
                });
            } else {
                user.roles.push(ROLE_ADMIN);
            }
            UserService.update(
                { id: user.id, email: user.email, roles: user.roles })
                .then(function (response) {
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    vm.loadAdmins();
                    vm.flipActivityIndicator('isSaving' + admin.id);

                })
                .catch(function (error) {
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                    vm.flipActivityIndicator('isSaving' + admin.id);
                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                admins: [],
            });
            vm.loadAdmins();
        };

    }
})(window.angular);
