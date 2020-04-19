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
            'ApplicationService',
            '$sce',
            '$modal',
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
        EnvironmentService,
        ApplicationService,
        $sce,
        $modal
    ) {
        var vm = this;

        vm.LOGIN_METHOD = CONFIG.LOGIN_METHOD;
        vm.selectedEnvironment = $localStorage.selectedEnvId.slice(0);
        vm.ownerTitle = "Owner Github :";
        var envName = "staging";

        if (vm.LOGIN_METHOD === 'proxy' || vm.LOGIN_METHOD === 'login') {
            vm.ownerTitle = "Owner Login Id :"
        }

        vm.setWatcherLink = function () {
             envName = "staging";

            vm.environments.forEach(element => {
                if (element.id === vm.selectedEnvironment) {
                    envName = element.name;
                }
            });
            if (vm.selectedEnvironment && vm.application && vm.application.name)
                vm.watcherLink = $sce.trustAsResourceUrl("https://kibana.leadwire.io/app/sentinl?securitytenant=" + envName + "-watcher-" + vm.application.name + "#/?embed=true");
        };


        vm.getBlob = function (data) {
            var a = document.createElement("a");
            a.href = "data:image/png;base64," + data;
            a.download = "Image.jpg";
            a.click();
            a.remove();
        }

        vm.getDate = function (data) {
            return data['@timestamp'];
        }

        vm.getWatchers = function () {

            envName = "staging";

            vm.environments.forEach(element => {
                if (element.id === vm.selectedEnvironment) {
                    envName = element.name;
                }
            });

            ApplicationService.getApplicationWatchers(vm.application.name, envName)
                .then(function (response) {
                    vm.watchers = response;
                }).catch(function (error) {
                    toastr.success(MESSAGES_CONSTANTS.ERROR);
                });
        }

        vm.deleteWatcher = function (_id, _index, ind) {

            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        ApplicationService.deleteApplicationWatcher(_id, _index)
                            .then(function (response) {
                                vm.watchers = vm.watchers.filter((_, index) => index !== ind)
                                toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                            }).catch(function (error) {
                                toastr.success(MESSAGES_CONSTANTS.ERROR);
                            });
                    } else {
                        swal.close();
                    }
                });
        }


        vm.hasReportsRule = function () {
            var access = false;
            if (vm.currentUser) {
                Object.keys(vm.currentUser.acl).forEach(element => {
                    if (vm.currentUser.acl[element][vm.application.id].REPORT) {
                        access = true;
                    }
                });
            }
            return access;
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
                        toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    })
                    .catch(function (error) {
                        toastr.success(MESSAGES_CONSTANTS.ERROR);
                        vm.flipActivityIndicator('isLoading');
                        vm.getApp();
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
                        toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    })
                    .catch(function (error) {
                        toastr.success(MESSAGES_CONSTANTS.ERROR);
                        vm.flipActivityIndicator('isLoading');
                        vm.getApp();
                    });
            }


        }

        vm.getEnvironmentsForReport = function () {

            if (vm.application && $rootScope.user.id === vm.application.owner.id) {
                return vm.environments;
            }

            var list = [];

            vm.environments.forEach(environment => {
                if (angular.isDefined(vm.currentUser.acl[environment.id][vm.application.id].REPORT)) {
                    list.push(environment);
                    vm.selectedEnvironment = environment.id;
                }
            });

            return list;
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
                    vm.application.invitations.forEach(invitation => {
                        if (invitation.user && invitation.user.id === $rootScope.user.id) {
                            vm.currentUser = invitation.user;
                        }
                    });
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

        vm.addWatcher = function(){
            var modal = $modal.open({
                size: 'lg',
                templateUrl: 'application/watcher/add.html',
                controller: 'AddWatcherCtrl',
                controllerAs: 'ctrl'
            });

            modal.appId = vm.application.id;
            modal.envName = envName;
            modal.appName = vm.application.name;
            modal.envId = vm.selectedEnvironment;

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
                currentUser: null
            });
            vm.getEnvList();
            vm.getApp();
            vm.loadStats();
        };
    }
})(window.angular, window.swal, window.moment);
