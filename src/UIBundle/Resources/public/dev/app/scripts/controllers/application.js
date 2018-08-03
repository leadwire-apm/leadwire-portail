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
    $rootScope, ApplicationFactory, toastr, MESSAGES_CONSTANTS,
) {
    $rootScope.currentNav = 'settings';

    var vm = this;
    vm.ui = {
        isDeleting: false,
    };

    ApplicationFactory.findAll().then(function(response) {
        vm.apps = response.data;
    });

    vm.deleteApp = function(id) {
        vm.flipActivityIndicator(id);
        ApplicationFactory.remove(id).then(function(res) {
            toastr.success(MESSAGES_CONSTANTS.DELETE_APP_SUCCESS);
            vm.flipActivityIndicator(id);
        }).catch(function(error) {
            toastr.error(
                error.message || MESSAGES_CONSTANTS.DELETE_APP_SUCCESS ||
                MESSAGES_CONSTANTS.ERROR);
            vm.flipActivityIndicator(id);
        });
    };

    vm.flipActivityIndicator = function(suffix) {
        var suffix = (typeof suffix !== 'undefined') ? suffix : '';
        vm.ui['isDeleting' + suffix] = !vm.ui['isDeleting' + suffix];
    };

}

function applicationDetailCtrlFN(
    ApplicationFactory, Invitation, $stateParams, $rootScope, CONFIG, toastr,
    MESSAGES_CONSTANTS) {
    var vm = this;
    init();
    ApplicationFactory.get($stateParams.id).then(function(res) {
        vm.app = res.data;
    });

    vm.handleInviteUser = function() {
        vm.flipActivityIndicator();
        Invitation.save({
            email: vm.invitedUser.email,
            app: vm.app,
        }).then(function(res) {
            toastr.success(MESSAGES_CONSTANTS.INVITE_USER_SUCCESS);
            vm.flipActivityIndicator();

            //TODO Handle success and failure
        }).catch(function(error) {
            vm.flipActivityIndicator();
            toastr.error(
                error.message || MESSAGES_CONSTANTS.INVITE_USER_FAILURE ||
                MESSAGES_CONSTANTS.ERROR);
            console.log(error);
        });
    };

    vm.flipActivityIndicator = function(suffix) {
        var suffix = (typeof suffix !== 'undefined') ? suffix : '';
        vm.ui['isSaving' + suffix] = !vm.ui['isSaving' + suffix];
    };

    function init() {
        $rootScope.currentNav = 'settings';
        vm.ui = {
            isSaving: false,
        };
        vm.UPLOAD_URL = CONFIG.UPLOAD_URL;
    };

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