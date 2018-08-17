(function(angular, swal) {
    angular
        .module('leadwireApp')
        .controller('addApplicationCtrl', addApplicationCtrlFN)
        .controller('applicationListCtrl', applicationListCtrlFN)
        .controller('applicationDetailCtrl', applicationDetailCtrlFN)
        .controller('applicationEditCtrl', applicationEditCtrlFN)
        .controller('activationModalCtrl', activationModalCtrl);

    /**
     * Handle add new application logic
     *
     * @param ApplicationFactory
     * @param ApplicationService
     * @param ApplicationTypeFactory
     * @param toastr
     * @param MESSAGES_CONSTANTS
     * @param $state
     */
    function addApplicationCtrlFN(
        ApplicationFactory,
        ApplicationService,
        ApplicationTypeFactory,
        toastr,
        MESSAGES_CONSTANTS,
        $state
    ) {
        var vm = this;
        init();
        vm.saveApp = function() {
            vm.flipActivityIndicator();
            ApplicationFactory.save(vm.application)
                .then(ApplicationService.handleSaveOnSuccess)
                .then(function() {
                    vm.flipActivityIndicator();
                    $state.go('app.applicationsList');
                })
                .catch(handleOnFailure);
        };

        vm.flipActivityIndicator = function() {
            vm.ui.isSaving = !vm.ui.isSaving;
        };

        function init() {
            vm.ui = {
                isSaving: false
            };

            ApplicationTypeFactory.findAll().then(function(response) {
                vm.applicationTypes = response.data;
            });
        }

        function handleOnFailure(error) {
            toastr.error(
                error.message ||
                    MESSAGES_CONSTANTS.ADD_APP_FAILURE ||
                    MESSAGES_CONSTANTS.ERROR
            );
            vm.flipActivityIndicator();
        }
    }

    function applicationListCtrlFN(
        $rootScope,
        ApplicationFactory,
        toastr,
        MESSAGES_CONSTANTS,
        $localStorage,
        $modal
    ) {
        var vm = this;
        init();
        vm.deleteApp = function(id) {
            swal({
                title: 'Are you sure?',
                text: 'Once deleted, you will not be able to recover this App!',
                icon: 'warning',
                buttons: true,
                dangerMode: true
            }).then(function(willDelete) {
                if (willDelete) {
                    ApplicationFactory.remove(id)
                        .then(function() {
                            getApps();
                            swal.close();
                            toastr.success(
                                MESSAGES_CONSTANTS.DELETE_APP_SUCCESS
                            );
                        })
                        .catch(function(error) {
                            swal.close();

                            toastr.error(
                                error.message ||
                                    MESSAGES_CONSTANTS.DELETE_APP_FAILURE ||
                                    MESSAGES_CONSTANTS.ERROR
                            );
                        });
                } else {
                    swal('Your App is safe!');
                }
            });
        };

        vm.flipActivityIndicator = function(suffix) {
            suffix = typeof suffix !== 'undefined' ? suffix : '';
            vm.ui['isDeleting' + suffix] = !vm.ui['isDeleting' + suffix];
        };

        function getApps() {
            // get all
            ApplicationFactory.findAll().then(function(response) {
                vm.apps = response.data;
                $localStorage.applications = response.data;
            });
        }

        function init() {
            $rootScope.currentNav = 'settings';
            vm.ui = {
                isDeleting: false
            };
            getApps();
        }

        vm.enableApp = function(app) {
            $modal.open({
                templateUrl:
                    $rootScope.ASSETS_BASE_URL + 'views/application/enable.html',
                controller: 'activationModalCtrl',
                controllerAs: 'ctrl',
                resolve: {
                    app: function() {
                        return app;
                    },
                    vm: function () {
                        return vm;
                    },
                    getApps: function() {
                      return getApps;
                    }
                }
            });
        };
    }

    function applicationDetailCtrlFN(
        ApplicationFactory,
        Invitation,
        $stateParams,
        $rootScope,
        CONFIG,
        toastr,
        MESSAGES_CONSTANTS
    ) {
        var vm = this;
        init();

        vm.handleInviteUser = function() {
            vm.flipActivityIndicator();
            Invitation.save({
                email: vm.invitedUser.email,
                app: {
                    id: vm.app.id
                }
            })
                .then(function() {
                    toastr.success(MESSAGES_CONSTANTS.INVITE_USER_SUCCESS);
                    getApp();
                    vm.flipActivityIndicator();
                })
                .catch(function(error) {
                    vm.flipActivityIndicator();
                    toastr.error(
                        error.message ||
                            MESSAGES_CONSTANTS.INVITE_USER_FAILURE ||
                            MESSAGES_CONSTANTS.ERROR
                    );
                });
        };

        vm.deleteInvitation = function(id) {
            swal({
                title: 'Are you sure?',
                text:
                    'Once deleted, you will not be able to recover this Invitation!',
                icon: 'warning',
                buttons: true,
                dangerMode: true
            }).then(function(willDelete) {
                if (willDelete) {
                    Invitation.remove(id)
                        .then(function() {
                            swal.close();
                            toastr.success(
                                MESSAGES_CONSTANTS.DELETE_INVITATION_SUCCESS
                            );
                            getApp();
                        })
                        .catch(function(error) {
                            swal.close();
                            toastr.error(
                                error.message ||
                                    MESSAGES_CONSTANTS.DELETE_INVITATION_FAILURE ||
                                    MESSAGES_CONSTANTS.ERROR
                            );
                        });
                } else {
                    swal('Your Invitation is safe!');
                }
            });
        };

        vm.flipActivityIndicator = function(suffix) {
            suffix = typeof suffix !== 'undefined' ? suffix : '';
            vm.ui['isSaving' + suffix] = !vm.ui['isSaving' + suffix];
        };

        function getApp() {
            ApplicationFactory.get($stateParams.id).then(function(res) {
                vm.app = res.data;
            });
        }

        function init() {
            $rootScope.currentNav = 'settings';
            vm.ui = {
                isSaving: false
            };
            vm.DOWNLOAD_URL = CONFIG.DOWNLOAD_URL;
            getApp();
        }
    }

    function applicationEditCtrlFN(
        ApplicationFactory,
        $stateParams,
        $rootScope,
        toastr,
        MESSAGES_CONSTANTS
    ) {
        var vm = this;
        $rootScope.currentNav = 'settings';
        vm.ui = {
            isSaving: false,
            isEditing: true
        };

        ApplicationFactory.get($stateParams.id).then(function(res) {
            vm.application = res.data;
        });

        vm.editApp = function() {
            vm.flipActivityIndicator();
            ApplicationFactory.update(vm.application.id, vm.application)
                .then(function() {
                    vm.flipActivityIndicator();
                    toastr.success(MESSAGES_CONSTANTS.EDIT_APP_SUCCESS);
                })
                .catch(function(error) {
                    vm.flipActivityIndicator();
                    toastr.error(
                        error.message ||
                            MESSAGES_CONSTANTS.EDIT_APP_FAILURE ||
                            MESSAGES_CONSTANTS.ERROR
                    );
                });
        };

        vm.flipActivityIndicator = function() {
            vm.ui.isSaving = !vm.ui.isSaving;
        };
    }


    function activationModalCtrl(toastr, ApplicationFactory, $modalInstance, MESSAGES_CONSTANTS, app, vm, getApps)
    {
        let ctrl = this;
        ctrl.enable = function () {
            console.log("hallaluia");
            ApplicationFactory.activate(app.id, ctrl.activationForm.activationCode)
                .then(function() {
                    toastr.success(MESSAGES_CONSTANTS.ACTIVATE_APP_SUCCESS);
                    $modalInstance.close();
                    getApps();
                })
                .catch(function(error) {
                    toastr.error(
                        error.message ||
                        MESSAGES_CONSTANTS.EDIT_APP_FAILURE ||
                        MESSAGES_CONSTANTS.ERROR
                    );
                });
        };

    }

})(window.angular, window.swal);
