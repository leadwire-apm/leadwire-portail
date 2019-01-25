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
                            vm.flipActivityIndicator('isDeleting' + id);
                            UserService.delete(id)
                                .then(function (response) {
                                    vm.flipActivityIndicator('isDeleting' + id);
                                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);

                                })
                                .catch(function (err) {
                                    vm.flipActivityIndicator('isDeleting' + id);
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
                            if (!message) {
                                throw null;
                            }
                            return vm.toggleUserStatus(user.id, message);
                        })
                        .catch(function (err) {
                            swal.close();
                        });
                }
            };

            vm.toggleUserStatus = function (id, message) {
                return UserService.toggleStatus(id, message)
                    .then(function (response) {
                        toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                        return response;
                    })
                    .then(vm.loadUsers)
                    .catch(function (err) {
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

            vm.init = function () {
                vm = angular.extend(vm, {
                    ui: {
                        isSaving: false,
                        isLoading: false,
                    },
                    users: [],
                });
                vm.loadUsers();
            };
        }
    }

)(window.angular, window.swal);
