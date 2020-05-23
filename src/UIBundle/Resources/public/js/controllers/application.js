(function (angular, swal) {
    angular.module('leadwireApp').controller('applicationListCtrl', [
        '$scope',
        'EnvironmentService',
        'ApplicationFactory',
        'toastr',
        'MESSAGES_CONSTANTS',
        '$localStorage',
        'Paginator',
        '$modal',
        'CONFIG',
        applicationListCtrlFN,
    ]);

    function applicationListCtrlFN(
        $scope,
        EnvironmentService,
        ApplicationFactory,
        toastr,
        MESSAGES_CONSTANTS,
        $localStorage,
        Paginator,
        $modal,
        CONFIG,
    ) {
        var vm = this;

        vm.LEADWIRE_LOGIN_METHOD = CONFIG.LEADWIRE_LOGIN_METHOD;

        vm.isAdmin = function(user) {
            return user.roles.indexOf("ROLE_SUPER_ADMIN") >= 0 || user.roles.indexOf("ROLE_ADMIN") >= 0;
        }

        if (!$localStorage.envList)
            EnvironmentService.list();

        if (!$localStorage.listApp) {
            ApplicationFactory.findAll()
                .then(function (res) {
                    $localStorage.listApp = res.data.reduce(function (p, c, i) {
                        p.push(c.name);
                        return p;
                    }, []);
                });
        }


        function getApps() {
            // get all
            vm.flipActivityIndicator('isLoading');
            ApplicationFactory.findMyApplications().then(function (response) {
                vm.flipActivityIndicator('isLoading');
                vm.apps = vm.paginator.items = response.data;
                $scope.$emit('set:apps', vm.apps);
                $localStorage.applications = vm.apps;
            }).catch(function () {
                vm.flipActivityIndicator('isLoading');
                vm.apps = [];
                vm.paginator.items = vm.apps;
            });
        }

        vm.deleteApp = function (id) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_DELETE_MODE).then(function (
                willDelete,
            ) {
                if (willDelete) {
                    ApplicationFactory.delete(id).then(function () {
                        vm.getApps();
                        swal.close();
                        toastr.success(MESSAGES_CONSTANTS.DELETE_APP_SUCCESS);
                    }).catch(function (error) {
                        swal.close();

                        toastr.error(
                            error.message ||
                            MESSAGES_CONSTANTS.DELETE_APP_FAILURE ||
                            MESSAGES_CONSTANTS.ERROR,
                        );
                    });
                } else {
                    swal('Your App is safe!');
                }
            });
        };

        vm.removeApp = function (id) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_REMOVE_MODE).then(function (
                willDelete,
            ) {
                if (willDelete) {
                    ApplicationFactory.remove(id).then(function () {
                        vm.getApps();
                        swal.close();
                        toastr.success(MESSAGES_CONSTANTS.REMOVE_APP_SUCCESS);
                    }).catch(function (error) {
                        swal.close();

                        toastr.error(
                            error.message ||
                            MESSAGES_CONSTANTS.DELETE_APP_FAILURE ||
                            MESSAGES_CONSTANTS.ERROR,
                        );
                    });
                } else {
                    swal('Your App is safe!');
                }
            });
        };

        vm.flipActivityIndicator = function (activity) {
            vm.ui[activity] = !vm.ui[activity];
        };

        vm.enableApp = function (selectedApp) {
            $modal.open({
                templateUrl: 'application/enable.html',
                controller: function ($modalInstance, $state) {
                    var modalVM = this;
                    modalVM.enable = function () {
                        ApplicationFactory.activate(selectedApp.id,
                            modalVM.activationCode).then(function (response) {
                                if (response.data) {
                                    toastr.success(
                                        MESSAGES_CONSTANTS.ACTIVATE_APP_SUCCESS);
                                    var updatedApp = angular.extend(selectedApp, {
                                        enabled: true,
                                    });
                                    $scope.$emit('activate:app', updatedApp);
                                    vm.apps = vm.apps.map(function (currentApp) {
                                        return currentApp.id !== selectedApp.id
                                            ? currentApp
                                            : updatedApp;
                                    });
                                    $localStorage.applications = vm.apps;
                                    $state.go('app.applicationDetail', {
                                        id: selectedApp.id,
                                    });
                                    $modalInstance.close();
                                } else {
                                    toastr.error(
                                        MESSAGES_CONSTANTS.ACTIVATE_APP_FAILURE);
                                }
                            }).catch(function (error) {
                                toastr.error(
                                    error.message ||
                                    MESSAGES_CONSTANTS.EDIT_APP_FAILURE ||
                                    MESSAGES_CONSTANTS.ERROR,
                                );
                            });
                    };
                },
                controllerAs: 'ctrl',
            });
        };

        $scope.$on('environment:updated', function (event, data) {
            vm.init();
        });

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {},
                paginator: Paginator.create({
                    itemsPerPage: 5,
                }),
            });
            vm.selectedEnvId = $localStorage.selectedEnvId;
            vm.getApps = getApps;
            vm.getApps();
        };
    }
})(window.angular, window.swal);
