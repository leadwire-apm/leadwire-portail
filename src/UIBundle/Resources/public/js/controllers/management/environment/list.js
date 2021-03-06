(function (angular) {
    angular.module('leadwireApp')
        .controller('ListEnvironmentController', [
            'EnvironmentService',
            'toastr',
            'MESSAGES_CONSTANTS',
            'ApplicationFactory',
            '$localStorage',
            ListEnvironmentCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ListEnvironmentCtrlFN(
        EnvironmentService,
        toastr,
        MESSAGES_CONSTANTS,
        ApplicationFactory,
        $localStorage,
    ) {
        var vm = this;

        if (!$localStorage.listApp) {
            ApplicationFactory.findAll()
                .then(function (res) {
                    $localStorage.listApp = res.data.reduce(function (p, c, i) {
                        p.push(c.name);
                        return p;
                    }, []);
                });
        }


        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.handleOnDelete = function (id) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        vm.delete(id);
                    } else {
                        swal.close();
                    }
                });

        };


        vm.loadEnvironments = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            EnvironmentService.list()
                .then(function (environments) {
                    vm.flipActivityIndicator('isLoading');
                    vm.environments = environments;
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');

                });
        };

        vm.delete = function (id) {
            EnvironmentService.delete(id)
                .then(function () {
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                })
                .then(vm.loadEnvironments)
                .catch(function () {
                    toastr.success(MESSAGES_CONSTANTS.ERROR);
                });
        };

        vm.setDefault = function (id) {
            EnvironmentService.setDefault(id)
                .then(function () {
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                })
                .then(vm.loadEnvironments)
                .catch(function () {
                    toastr.success(MESSAGES_CONSTANTS.ERROR);
                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                environments: [],
            });
            vm.loadEnvironments();
        };

    }
})(window.angular);
