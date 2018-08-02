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

function SettingsCtrl(
    $localStorage, Account, $location, $scope, $rootScope, FileService) {

    var ctrl = this;
    let _ctrl = new Ctrl(Account, $scope, $location, $localStorage, this,
        false, $rootScope, FileService);
    this.save = _ctrl.save;
}

function Ctrl(
    Account, $scope, $location, $localStorage, Controller, $modalInstance,
    $rootScope, FileService) {

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
                    if (Controller.avatar) {
                        FileService.upload(Controller.avatar, 'user').
                            then(function(response) {
                                console.log(response);
                                Account.updateProfile({
                                    id: Controller.user.id,
                                    avatar: response.data.name,
                                });
                                Controller.handleSuccessForm();
                            });

                    } else {
                        Controller.handleSuccessForm();
                    }

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

    Controller.handleSuccessForm = function handleSuccess() {
        $localStorage.user = Controller.user;
        if (!!$modalInstance) {
            $modalInstance.close();
        } else {
            $location.path('/');
        }
    };

}
