'use strict';

angular
    .module('leadwireApp')
    .controller('SettingsModal', SettingsModal)
    .controller('settingsCtrl', SettingsCtrl);

function SettingsModal($localStorage, Account, $location, $scope, isModal, $modalInstance) {
    var ctrl = this;
    let _ctrl = new Ctrl(Account, $scope, $location, $localStorage, this, isModal, $modalInstance);
    this.save = _ctrl.save;
}

function SettingsCtrl ($localStorage, Account, $location, $scope) {

    var ctrl = this;
    let _ctrl = new Ctrl(Account, $scope, $location, $localStorage, this, false, false);
    this.save = _ctrl.save;
}

function Ctrl(Account, $scope, $location, $localStorage, Controller, isModal,  $modalInstance) {

    Controller.user = $localStorage.user ? $localStorage.user : Account.getProfile();
    Controller.showCheckBoxes = !!Controller.email;

    this.save = function save () {
        if ($scope.userForm.$valid) {
            Account
                .updateProfile({
                    id: Controller.user.id,
                    email: Controller.user.email,
                    company: Controller.user.company,
                    acceptNewsLetter: Controller.user.acceptNewsLetter,
                    contact: Controller.user.contact.trim(),
                    contactPreference: Controller.user.contactPreference.trim()
                })
                .success(function (data) {
                    if (data) {
                        $localStorage.user = Controller.user;
                        console.log(isModal);
                        if (isModal) {
                            $modalInstance.close();
                        } else
                            $location.path('/');
                    }
                    else {
                        alert('Failed update User');
                        $location.path('/');
                    }
                })
                .error(function (error) {
                    console.log(error);
                });
        }
    };
}
