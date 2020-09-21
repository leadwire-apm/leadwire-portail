(function (angular, swal) {
    angular.module('leadwireApp')
        .controller('ManageUsersController', [
            'UserService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            ManageUsersCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ManageUsersCtrlFN (
        UserService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.handleDeleteUser = function (id) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        vm.flipActivityIndicator('isProcessing');
                        UserService.delete(id)
                            .then(function (response) {
                                vm.flipActivityIndicator('isProcessing');
                                toastr.success(MESSAGES_CONSTANTS.SUCCESS);

                            })
                            .catch(function (err) {
                                vm.flipActivityIndicator('isProcessing');
                                toastr.error(MESSAGES_CONSTANTS.ERROR);
                            });
                    } else {
                        swal.close();
                    }
                });
        };

        vm.handleOnToggleLock = function (user) {
            if (user.locked) {
                // UnLock the user
                swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                    .then(function (willDelete) {
                        if (willDelete) {
                            vm.toggleUserStatus(user.id);
                        } else {
                            swal.close();
                        }
                    });

            } else {
                // Block the user
                swal(MESSAGES_CONSTANTS.SWEET_ALERT_WITH_INPUT(
                    'Please enter a message to show when the user tries to login'))
                    .then(function (message) {
                        return vm.toggleUserStatus(user.id, message);
                    })
                    .then(() => {
                        swal.close();
                    })
                    .catch(function (err) {
                        swal.close();
                    });
            }
        };

        vm.isAdmin = function (admin) {
            return (admin && admin.roles &&
                (admin.roles.indexOf(UserService.getRoles().ADMIN) !== -1 ||
                    admin.roles.indexOf(
                        UserService.getRoles().SUPER_ADMIN) !== -1));
        };

        vm.handleChangePermission = function (admin) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        vm.changePermission(admin);
                        swal.close();
                    } else {
                        swal.close();
                    }
                });
        };

        vm.changePermission = function (admin) {
            vm.flipActivityIndicator('isProcessing');
            const user = angular.extend({}, admin);
            if (vm.isAdmin(user)) {
                user.roles = user.roles.filter(function (role) {
                    return role !== UserService.getRoles().ADMIN;
                });
            } else {
                user.roles.push(UserService.getRoles().ADMIN);
            }
            UserService.update(
                { id: user.id, email: user.email, roles: user.roles, name: user.name })
                .then(function (response) {
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    vm.flipActivityIndicator('isProcessing');
                })
                .then(vm.loadUsers)
                .catch(function (error) {
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                    vm.flipActivityIndicator('isProcessing');
                });
        };

        vm.toggleUserStatus = function (id, message) {
            vm.flipActivityIndicator('isProcessing');
            return UserService.toggleStatus(id, message)
                .then(function (response) {
                    vm.flipActivityIndicator('isProcessing');
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    return response;
                })
                .then(vm.loadUsers)
                .catch(function (err) {
                    vm.flipActivityIndicator('isProcessing');
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                    vm.flipActivityIndicator('isSaving');
                });
        };

        vm.loadUsers = function () {
            vm.flipActivityIndicator('isLoading');
            UserService.list()
                .then(function (users) {
                    vm.flipActivityIndicator('isLoading');
                    vm.users = users;
                })
                .catch(function (err) {
                    vm.flipActivityIndicator('isLoading');
                    vm.users = [];
                });
        };

        vm.goDetail = function (id) {
            $state.go('app.management.userDetail', {
                id: id,
            });
        };

        vm.goManageApplications = function (id) {
            $state.go('app.management.userManageApplications', {
                id: id,
            });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                    isProcessing: false,
                },
                onlyAdmins: false,
                ADMINS: UserService.ADMINS,
                users: [],
            });
            vm.loadUsers();
        };
    }
}

)(window.angular, window.swal);
