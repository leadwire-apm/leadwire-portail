(function (angular, swal) {
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

            vm.handleDeleteUser = function (id) {

                swal({
                    title: 'Are you sure?',
                    className: 'text-center',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true
                }).then(function (willDelete) {
                    if (willDelete) {
                        vm.flipActivityIndicator('isDeleting' + id);
                        UserService.delete(id).then(function (response) {
                            vm.flipActivityIndicator('isDeleting' + id);
                            toastr.success(MESSAGES_CONSTANTS.SUCCESS);

                        }).catch(function (err) {
                            vm.flipActivityIndicator('isDeleting' + id);
                            toastr.error(MESSAGES_CONSTANTS.ERROR);
                        })
                    } else {
                        swal.close()
                    }
                })
            };

            vm.handleOnEnable = function (user) {
                if (user.active) {
                    swal({
                        text: 'The message to show when the user tries to login',
                        content: "input",
                        button: {
                            text: "Submit",
                            closeModal: false,
                        },
                    })
                        .then(function (message) {
                            if (!message) {
                                throw null;
                            }
                            return vm.enableUser(user.id, message)
                        })
                        .then(function (response) {
                            swal({
                                title: MESSAGES_CONSTANTS.SUCCESS
                            })
                        })
                        .catch(function (err) {
                            console.log(err)
                            if (err) {
                                swal("Oh noes!", "The AJAX request failed!", "error");
                            } else {
                                swal.stopLoading();
                                swal.close();
                            }
                        });

                } else {
                    vm.enableUser(user.id)
                }

            };


            vm.enableUser = function (id, message) {
                return UserService.enable(id, message)
                    .then(function (response) {
                        return response
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
                    // TODO Remove This
                    vm.users = [
                        {id: 1, name: 'Ibra', email: 'ibra@gmail.com', active: true},
                        {id: 2, name: 'dali', email: 'dali@gmail.com', active: false},
                        {id: 3, name: 'omar', email: 'omar@gmail.com', active: true},
                    ]
                })
            }
            vm.goDetail = function (id) {
                $state.go('app.management.userDetail', {
                    id: id
                });
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
    }

)(window.angular, window.swal);
