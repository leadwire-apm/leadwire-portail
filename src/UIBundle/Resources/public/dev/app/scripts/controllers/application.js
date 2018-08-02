angular.module('leadwireApp').
    controller('formApplicationCtrl',
        formApplicationCtrlFN).
    controller('applicationListCtrl',
        applicationListCtrlFN).
    controller('applicationDetailCtrl', applicationDetailCtrlFN).
    controller('applicationEditCtrl', applicationEditCtrlFN);

function formApplicationCtrlFN(
    $sce,
    ConfigService,
    Application,
    $location,
    $localStorage,
    $rootScope,
) {
    var vm = this;
    $rootScope.currentNav = 'application';
    vm.saveApp = function() {
        // TODO
        Application.save(vm.application).then(function(res) {
            console.log();
        });
    };

}

function applicationListCtrlFN(
    $rootScope,
    Application,
) {
    var vm = this;
    $rootScope.currentNav = 'settings';
    Application.findMyApps().then(function(response) {
        console.log(response.data);
        vm.apps = response.data;
    });

}

function applicationDetailCtrlFN(
    Application, Invitation, $stateParams, CONFIG) {
    //Application.get()
    var vm = this;
    vm.UPLOAD_URL = CONFIG.UPLOAD_URL;
    Application.get($stateParams.id).then(function(res) {
        console.log(res.data);
        vm.app = res.data;
    });

    vm.handleInviteUser = function() {
        Invitation.save({
            email: vm.invitedUser.email,
            app: vm.app,
        }).then(function(res) {
            //TODO Handle success and failure
            console.log(res);
        }).catch(function(error) {
            console.log(error);
        });
    };
}

function applicationEditCtrlFN(Application, $stateParams) {
    var vm = this;

    Application.get($stateParams.id).then(function(res) {
        vm.application = res.data;
    });

    vm.editApp = function() {
        Application.update(vm.application.id, vm.application).
            then(function(res) {
                console.log(res);
            });
    };

}