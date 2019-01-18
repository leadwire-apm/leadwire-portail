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
        var ROLE_ADMIN = 'ROLE_ADMIN';

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


        vm.loadAdmins = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            UserService.list().then(function (response) {
                vm.flipActivityIndicator('isLoading');
                vm.users = response.data;
            }).catch(function (err) {
                vm.flipActivityIndicator('isLoading');
                // TODO Remove this
                vm.admins = [
                    {id: 1, name: 'Ibra', email: 'ibra@gmail.com', active: true, role: ["ROLE_USER", "ROLE_ADMIN"]},
                    {id: 2, name: 'dali', email: 'dali@gmail.com', active: false, role: ["ROLE_USER"]},
                    {id: 3, name: 'omar', email: 'omar@gmail.com', active: true, role: ["ROLE_USER", "ROLE_ADMIN"]},
                ]

            })
        }


        vm.isAdmin = function (admin) {
            return (admin && admin.role && admin.role.indexOf(ROLE_ADMIN) !== -1);
        };

        vm.handleChangePermission = function (admin) {

            swal({
                title: 'Are you sure?',
                className: 'text-center',
                icon: 'warning',
                buttons: true,
                dangerMode: true
            }).then(function (willDelete) {
                if (willDelete) {
                    vm.changePermission(admin)
                } else {
                    swal.close()
                }
            })

        };

        vm.changePermission = function (admin) {
            vm.flipActivityIndicator('isSaving' + admin.id);
            const user = angular.extend({}, admin);
            if (vm.isAdmin(user)) {
                user.role = user.role.filter(function (role) {
                    return role !== ROLE_ADMIN;
                })
            } else {
                user.role.push(ROLE_ADMIN)
            }
            UserService.update(user).then(function (response) {
                toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                vm.loadAdmins();
                vm.flipActivityIndicator('isSaving' + admin.id);

            }).catch(function (error) {
                toastr.error(MESSAGES_CONSTANTS.ERROR);
                vm.flipActivityIndicator('isSaving' + admin.id);
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
