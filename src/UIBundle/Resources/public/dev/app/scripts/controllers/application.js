angular.module('leadwireApp').
    controller('addApplicationCtrl',
        addApplicationCtrlFN).
    controller('applicationListCtrl',
        applicationListCtrlFN).
    controller('applicationDetailCtrl', applicationDetailCtrlFN).
    controller('applicationEditCtrl', applicationEditCtrlFN);

/**
 * Handle add new application logic
 *
 * @param $sce
 * @param ConfigService
 * @param ApplicationFactory
 * @param $location
 * @param $localStorage
 * @param $rootScope
 * @param toastr
 * @param MESSAGES_CONSTANTS
 */
function addApplicationCtrlFN(
    $sce,
    ConfigService,
    ApplicationFactory,
    $location,
    $localStorage,
    $rootScope, toastr, MESSAGES_CONSTANTS,
) {
    var vm = this;
    vm.ui = {
        isSaving: false,
    };
    $rootScope.currentNav = 'settings';
    vm.saveApp = function() {
        vm.flipActivityIndicator();
        ApplicationFactory.save(vm.application).then(function(res) {
            toastr.success(MESSAGES_CONSTANTS.ADD_APP_SUCCESS);
            vm.flipActivityIndicator();
            $location.path('/');
        }).catch(function(error) {
            toastr.error(
                error.message || MESSAGES_CONSTANTS.ADD_APP_FAILURE ||
                MESSAGES_CONSTANTS.ERROR);
            vm.flipActivityIndicator();
        });
    };

    vm.flipActivityIndicator = function() {
        vm.ui.isSaving = !vm.ui.isSaving;
    };

}

function applicationListCtrlFN(
    $rootScope, ApplicationFactory, toastr, MESSAGES_CONSTANTS, $localStorage,
) {
    var vm = this;
    init();

    vm.deleteApp = function(id) {
        swal({
            title: 'Are you sure?',
            text: 'Once deleted, you will not be able to recover this App!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                ApplicationFactory.remove(id).then(function(res) {
                    getApps();
                    swal.close();
                    toastr.success(MESSAGES_CONSTANTS.DELETE_APP_SUCCESS);
                }).catch(function(error) {
                    swal.close();

                    toastr.error(
                        error.message ||
                        MESSAGES_CONSTANTS.DELETE_APP_FAILURE ||
                        MESSAGES_CONSTANTS.ERROR);
                });

            } else {
                swal('Your App is safe!');
            }
        });

    };

    vm.flipActivityIndicator = function(suffix) {
        var suffix = (typeof suffix !== 'undefined') ? suffix : '';
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
            isDeleting: false,
        };
        getApps();

    }

}

function applicationDetailCtrlFN(
    ApplicationFactory, Invitation, $stateParams, $rootScope, CONFIG, toastr,
    MESSAGES_CONSTANTS) {
    var vm = this;
    init();

    vm.handleInviteUser = function() {
        vm.flipActivityIndicator();
        Invitation.save({
            email: vm.invitedUser.email,
            app: {
                id: vm.app.id,
            },
        }).
            then(function(res) {
                toastr.success(MESSAGES_CONSTANTS.INVITE_USER_SUCCESS);
                vm.flipActivityIndicator();
            }).catch(function(error) {
            vm.flipActivityIndicator();
            toastr.error(
                error.message || MESSAGES_CONSTANTS.INVITE_USER_FAILURE ||
                MESSAGES_CONSTANTS.ERROR);
        });
    };

    vm.deleteInvitation = function(id) {
        swal({
            title: 'Are you sure?',
            text: 'Once deleted, you will not be able to recover this Invitation!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                Invitation.remove(id).then(function(res) {
                    swal.close();
                    toastr.success(
                        MESSAGES_CONSTANTS.DELETE_INVITATION_SUCCESS);
                    getApp();

                }).catch(function(error) {
                    swal.close();

                    toastr.error(
                        error.message ||
                        MESSAGES_CONSTANTS.DELETE_INVITATION_FAILURE ||
                        MESSAGES_CONSTANTS.ERROR);
                });

            } else {
                swal('Your Invitation is safe!');
            }
        });

    };

    vm.flipActivityIndicator = function(suffix) {
        var suffix = (typeof suffix !== 'undefined') ? suffix : '';
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
            isSaving: false,
        };
        vm.DOWNLOAD_URL = CONFIG.DOWNLOAD_URL;
        getApp();
    }

}

function applicationEditCtrlFN(
    ApplicationFactory, $stateParams, $rootScope, toastr, MESSAGES_CONSTANTS) {
    var vm = this;
    $rootScope.currentNav = 'settings';
    vm.ui = {
        isSaving: false,
    };

    ApplicationFactory.get($stateParams.id).then(function(res) {
        vm.application = res.data;
    });

    vm.editApp = function() {
        vm.flipActivityIndicator();
        ApplicationFactory.update(vm.application.id, vm.application).
            then(function(res) {
                vm.flipActivityIndicator();
                toastr.success(MESSAGES_CONSTANTS.EDIT_APP_SUCCESS);
            }).
            catch(function(error) {
                vm.flipActivityIndicator();
                toastr.error(
                    error.message || MESSAGES_CONSTANTS.EDIT_APP_FAILURE ||
                    MESSAGES_CONSTANTS.ERROR);
            });
    };

    vm.flipActivityIndicator = function() {
        vm.ui.isSaving = !vm.ui.isSaving;
    };

}