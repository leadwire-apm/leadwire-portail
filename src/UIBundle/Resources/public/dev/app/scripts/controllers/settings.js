'use strict';

angular
    .module('leadwireApp')
    .controller('settingsCtrl', ['$localStorage', 'Account', '$location', '$scope',
        function($localStorage, Account, $location, $scope) {

            var ctrl = this;
            ctrl.user = $localStorage.user ? $localStorage.user : Account.getProfile();
            ctrl.showCheckBoxes = !!ctrl.email;

            ctrl.save = function () {
                console.log(ctrl);
                if ($scope.userForm.$valid) {
                    Account
                        .updateProfile({
                            id: ctrl.user.id,
                            email: ctrl.user.email,
                            company: ctrl.user.company,
                        })
                        .success(function (data) {
                            if (data)
                                $localStorage.user = ctrl.user;
                            else {
                                alert('Failed update User');
                                $location.path('/');
                            }
                        })
                        .error(function (error) {
                            console.log(error);
                        });
                    $location.path('/');
                }
            };
        }
    ]);
