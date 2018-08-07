'use strict';

angular.module('leadwireApp').
    controller('SettingsModalCtrl', SettingsModalCtrl).
    controller('settingsCtrl', SettingsCtrl);

function SettingsModalCtrl(
    $localStorage, Account, $location, isModal, $modalInstance, toastr) {
    var vm = this;
    let _ctrl = new UserCtrl(Account, $location, $localStorage, vm,
        $modalInstance, toastr);
    this.save = _ctrl.save;
}

function SettingsCtrl(
    $localStorage, Account, $location, $rootScope, FileService, toastr) {

    var vm = this;
    let _ctrl = new UserCtrl(Account, $location, $localStorage, vm,
        false, $rootScope, FileService, toastr);
    this.save = _ctrl.save;
}

function UserCtrl(
    Account, $location, $localStorage, Controller, $modalInstance,
    $rootScope, FileService, toastr) {

    Controller.user = $localStorage.user ?
        $localStorage.user :
        Account.getProfile();
    Controller.showCheckBoxes = !!$modalInstance;
    this.save = function save() {
        if (Controller.userForm.$valid) {
            Account.updateProfile({
                id: Controller.user.id,
                email: Controller.user.email,
                company: Controller.user.company,
                acceptNewsLetter: Controller.user.acceptNewsLetter,
                contact: Controller.user.contact,
                contactPreference: Controller.user.contactPreference,
                username: Controller.user.username,
                name: Controller.user.name,
            }).success(function(data) {
                if (data) {
                    if (Controller.avatar) {
                        FileService.upload(Controller.avatar, 'user').
                            then(function(response) {
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
                    toastr.error('Failed update User');
                    // $location.path('/');
                }
            }).error(function(error) {
                console.log(error);
                toastr.error('Failed update User');

            });

        }
    };

    Controller.handleSuccessForm = function handleSuccess() {
        $localStorage.user = Controller.user;
        toastr.success('User has been updated successfully');
        if (!!$modalInstance) {
            $modalInstance.close();
        } else {
            $location.path('/');
        }
    };

}
