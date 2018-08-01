'use strict';

angular.module('leadwireApp').
    controller('SettingsModal', SettingsModal).
    controller('settingsCtrl', SettingsCtrl);

function SettingsModal(
    $localStorage, Account, $location, $scope, isModal, $modalInstance) {
    var ctrl = this;
    let _ctrl = new Ctrl(Account, $scope, $location, $localStorage, this,
        $modalInstance);
    this.save = _ctrl.save;
}

function SettingsCtrl($localStorage, Account, $location, $scope,$rootScope) {

    var ctrl = this;
    let _ctrl = new Ctrl(Account, $scope, $location, $localStorage, this,
        false,$rootScope);
    this.save = _ctrl.save;
}

function Ctrl(
    Account, $scope, $location, $localStorage, Controller, $modalInstance,$rootScope) {

    Controller.user = $localStorage.user ?
        $localStorage.user :
        Account.getProfile();
    Controller.showCheckBoxes = !!$modalInstance;
    $rootScope.currentNav = 'settings';
    this.save = function save() {
        if ($scope.userForm.$valid) {
            Account.updateProfile({
                id: Controller.user.id,
                email: Controller.user.email,
                company: Controller.user.company,
                acceptNewsLetter: Controller.user.acceptNewsLetter,
                contact: Controller.user.contact,
                contactPreference: Controller.user.contactPreference,
            }).success(function(data) {
                if (data) {
                    $localStorage.user = Controller.user;
                    console.log(isModal);
                    if (!!$modalInstance) {
                        $modalInstance.close();
                    } else
                        $location.path('/');
                }
                else {
                    alert('Failed update User');
                    $location.path('/');
                }
            }).error(function(error) {
                console.log(error);
            });
        }
    };
}
