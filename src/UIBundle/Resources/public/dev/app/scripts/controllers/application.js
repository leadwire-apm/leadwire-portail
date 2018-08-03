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
 */
function addApplicationCtrlFN(
    $sce,
    ConfigService,
    ApplicationFactory,
    $location,
    $localStorage,
    $rootScope,
) {
    var vm = this;
    vm.ui = {
        isSaving: false,
    };
    $rootScope.currentNav = 'settings';
    vm.saveApp = function() {
        // TODO
        vm.flipActivityIndicator();
        ApplicationFactory.save(vm.application).then(function(res) {
            vm.flipActivityIndicator();
        }).catch(function(error) {
            vm.flipActivityIndicator();
        });
    };

    vm.flipActivityIndicator = function() {
        vm.ui.isSaving = !vm.ui.isSaving;
    };

}

function applicationListCtrlFN(
    $rootScope,
    ApplicationFactory,
) {
    $rootScope.currentNav = 'settings';

    var vm = this;
    vm.ui = {
        isDeleting: false,
    };

    $rootScope.currentNav = 'settings';
    ApplicationFactory.findMyApps().then(function(response) {
        vm.apps = response.data;
    });

    vm.deleteApp = function(id) {
        vm.flipActivityIndicator(id);
        ApplicationFactory.remove(id).then(function(res) {
            // TODO : Handle delete success
            vm.flipActivityIndicator(id);
        }).catch(function(error) {
            //TODO Handle delete error
            vm.flipActivityIndicator(id);
        });
    };

    vm.flipActivityIndicator = function(suffix) {
        var suffix = (typeof suffix !== 'undefined') ? suffix : '';
        vm.ui['isDeleting' + suffix] = !vm.ui['isDeleting' + suffix];
    };

}

function applicationDetailCtrlFN(
    ApplicationFactory, Invitation, $stateParams, $rootScope, CONFIG) {
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
            vm.flipActivityIndicator();

            //TODO Handle success and failure
        }).catch(function(error) {
            vm.flipActivityIndicator();
            //TODO
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

function applicationEditCtrlFN(ApplicationFactory, $stateParams, $rootScope) {
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
                // TODO handle on edit
            }).
            catch(function(error) {
                vm.flipActivityIndicator();
                //TODO
            });
    };

    vm.flipActivityIndicator = function() {
        vm.ui.isSaving = !vm.ui.isSaving;
    };

}