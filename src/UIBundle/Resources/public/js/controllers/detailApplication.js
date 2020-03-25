(function (angular, swal, moment) {
    angular
        .module('leadwireApp')
        .controller('applicationDetailCtrl', [
            'ApplicationFactory',
            'InvitationFactory',
            '$stateParams',
            '$rootScope',
            'CONFIG',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$localStorage',
            'AccessLevelService',
            'EnvironmentService',
            applicationDetailCtrlFN,
        ]);

    function applicationDetailCtrlFN(
        ApplicationFactory,
        InvitationFactory,
        $stateParams,
        $rootScope,
        CONFIG,
        toastr,
        MESSAGES_CONSTANTS,
        $localStorage,
        AccessLevelService,
        EnvironmentService
    ) {
        var vm = this;

        vm.LOGIN_METHOD = CONFIG.LOGIN_METHOD;
        vm.selectedEnvironment = $localStorage.selectedEnvId;

        vm.ownerTitle = "Owner Github :"
        if (vm.LOGIN_METHOD === 'proxy' || vm.LOGIN_METHOD === 'login') {
            vm.ownerTitle = "Owner Login Id :"
        }

        vm.setAccess = function (user, access, level, report) {
            acl = {
                "user": user,
                "env": vm.selectedEnvironment,
                "app": vm.application.id,
                "level": level,
                "access": access
            };

            if (angular.isDefined(report) && !report) {
                AccessLevelService.deleteAccess(acl)
                    .then(function (response) {
                        var __app = { ...vm.application };
                        __app.invitations.forEach((element, id) => {
                            if (element.user && element.user.id === user) {
                                vm.application.invitations[id].user.acl = response.data.acls;
                            }
                        });

                    })
                    .catch(function (error) {
                        vm.flipActivityIndicator('isLoading');
                    });
            } else {
                AccessLevelService.setAccess(acl)
                    .then(function (response) {
                        var __app = { ...vm.application };
                        __app.invitations.forEach((element, id) => {
                            if (element.user && element.user.id === user) {
                                vm.application.invitations[id].user.acl = response.data.acls;
                            }
                        });

                    })
                    .catch(function (error) {
                        vm.flipActivityIndicator('isLoading');
                    });
            }


        }

        vm.filter = function (invitation) {
            if (angular.isDefined(invitation.user)) {
                return true;
            } else {
                return false;
            }
        }

        vm.getApp = function () {
            ApplicationFactory.get($stateParams.id)
                .then(function (res) {
                    vm.application = res.data;
                });
        };
        vm.loadStats = function () {
            ApplicationFactory.stats($stateParams.id)
                .then(function (response) {
                    vm.applicationStats = response.data;
                });
        };

        vm.handleInviteUser = function () {
            var invitedEmails = vm.application.invitations.map(function (invitation) {
                return invitation.email ? invitation.email.toLowerCase() : null;
            });
            if (
                invitedEmails.indexOf(vm.invitedUser.email.toLowerCase()) === -1
            ) {
                vm.flipActivityIndicator();
                InvitationFactory.save({
                    email: vm.invitedUser.email,
                    application: {
                        id: vm.application.id,
                    },
                })
                    .then(function () {
                        toastr.success(MESSAGES_CONSTANTS.INVITE_USER_SUCCESS);
                        vm.getApp();
                        vm.flipActivityIndicator();
                        vm.invitedUser.email = '';
                    })
                    .catch(function (error) {
                        vm.flipActivityIndicator();
                        toastr.error(
                            error.message ||
                            MESSAGES_CONSTANTS.INVITE_USER_FAILURE ||
                            MESSAGES_CONSTANTS.ERROR,
                        );
                    });
            } else {
                toastr.error(MESSAGES_CONSTANTS.INVITE_USER_VALIDATION);
            }
        };

        vm.deleteInvitation = function (id) {
            var body = document.createElement('h5');
            body.innerText =
                'Once deleted, you will not be able to recover this Invitation!';
            body.className = 'text-center';

            swal({
                title: 'Are you sure?',
                className: 'text-center',
                content: body,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            })
                .then(function (willDelete) {
                    if (willDelete) {
                        vm.ui.isDeleting = true;
                        InvitationFactory.remove(id)
                            .then(function () {
                                swal.close();
                                toastr.success(
                                    MESSAGES_CONSTANTS.DELETE_INVITATION_SUCCESS,
                                );
                                vm.getApp();
                                vm.ui.isDeleting = false;
                            })
                            .catch(function (error) {
                                swal.close();
                                toastr.error(
                                    error.message ||
                                    MESSAGES_CONSTANTS.DELETE_INVITATION_FAILURE ||
                                    MESSAGES_CONSTANTS.ERROR,
                                );
                            });
                    } else {
                        swal('Your Invitation is safe!');
                    }
                });
        };

        vm.flipActivityIndicator = function (suffix) {
            suffix = typeof suffix !== 'undefined' ? suffix : '';
            vm.ui['isSaving' + suffix] = !vm.ui['isSaving' + suffix];
        };

        vm.getEnvList = function () {
            EnvironmentService.list()
                .then(function (environments) {
                    vm.environments = environments;
                })
                .catch(function (error) {
                });
        }

        vm.onLoad = function () {
            $rootScope.currentNav = 'settings';
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isDeleting: false,
                },
                invitedUser: {
                    email: '',
                },
                environments: [],
                CONSTANTS: CONFIG,
                moment: moment,
                retention: CONFIG.STRIPE_ENABLED == true ? $rootScope.user.plan.retention : null,
                DOWNLOAD_URL: CONFIG.DOWNLOAD_URL,
            });
            vm.getEnvList();
            vm.getApp();
            vm.loadStats();
        };
    }
})(window.angular, window.swal, window.moment);
