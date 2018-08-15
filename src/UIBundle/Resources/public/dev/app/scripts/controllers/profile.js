'use strict';

angular
    .module('leadwireApp')
    .controller('profileModalCtrl', ProfileModalCtrl)
    .controller('profileCtrl', ProfileCtrl);

function ProfileModalCtrl(
    $localStorage,
    Account,
    $location,
    isModal,
    $modalInstance,
    FileService,
    toastr,
    CONFIG,
    $rootScope,
    CountryApi,
    $scope
) {
    var vm = this;
    let _ctrl = new UserCtrl(
        Account,
        $location,
        $localStorage,
        vm,
        FileService,
        toastr,
        $rootScope,
        $scope,
        CountryApi,
        $modalInstance
    );
    this.save = _ctrl.save;
}

function ProfileCtrl(
    $localStorage,
    Account,
    $location,
    FileService,
    toastr,
    $rootScope,
    CountryApi,
    $scope
) {
    var vm = this;
    let _ctrl = new UserCtrl(
        Account,
        $location,
        $localStorage,
        vm,
        FileService,
        toastr,
        $rootScope,
        $scope,
        CountryApi,

        false
    );
    this.save = _ctrl.save;

}

function UserCtrl(
    Account,
    $location,
    $localStorage,
    Controller,
    FileService,
    toastr,
    $rootScope,
    $scope,
    CountryApi,
    $modalInstance
) {
    Controller.user = angular.extend({}, $localStorage.user);
    var sep = '###';
    Controller.showCheckBoxes = !!$modalInstance;
    this.save = function save() {
        if (Controller.userForm.$valid) {
            var phone = Controller.user.contact
                ? Controller.user.phoneCode + sep + Controller.user.contact
                : null;
            var updatedInfo = {
                id: Controller.user.id,
                email: Controller.user.email,
                company: Controller.user.company,
                acceptNewsLetter: Controller.user.acceptNewsLetter,
                contact: phone,
                contactPreference: Controller.user.contactPreference,
                defaultApp: Controller.user.defaultApp, //TODO UNCOMMENT ME WHEN ITS FIXED
                username: Controller.user.username,
                name: Controller.user.name
            };
            Account.updateProfile(updatedInfo)
                .success(function(data) {
                    $localStorage.user = angular.extend(
                        $localStorage.user,
                        updatedInfo,
                        {
                            contact: Controller.user.contact,
                            phoneCode: Controller.user.phoneCode
                        }
                    );

                    if (data) {
                        if (Controller.avatar) {
                            FileService.upload(Controller.avatar, 'user').then(
                                function(response) {
                                    Account.updateProfile({
                                        id: Controller.user.id,
                                        avatar: response.data.name
                                    });
                                    $localStorage.user = angular.extend(
                                        $localStorage.user,
                                        {
                                            avatar: response.data.name
                                        }
                                    );
                                    Controller.handleSuccessForm(
                                        response.data.name
                                    );
                                }
                            );
                        } else {
                            Controller.handleSuccessForm();
                        }
                    } else {
                        toastr.error('Failed update User');
                    }
                })
                .error(function(error) {
                    console.log(error);
                    toastr.error('Failed update User');
                });
        }
    };

    Controller.handleSuccessForm = function handleSuccess(newImage) {
        $localStorage.user = Controller.user;
        toastr.success('User has been updated successfully');
        if ($modalInstance !== false) {
            $modalInstance.close();
        } else {
            if (newImage) {
                $scope.$emit('update-image', newImage);
            }
            $location.path('/');
        }
    };

    if (!$localStorage.countries) {
        CountryApi.getAll().then(function(res) {
            $localStorage.countries = res.data.map(function(country) {
                return angular.extend(country, {
                    phoneCode: country.callingCodes[0],
                    label: country.alpha2Code + ' ' + country.callingCodes[0]
                });
            });
            $rootScope.countries = $localStorage.countries;
        });
    } else {
        $rootScope.countries = $localStorage.countries;
    }

}
