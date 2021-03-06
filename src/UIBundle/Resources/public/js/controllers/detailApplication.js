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
            'DashboardService',
            '$modal',
            'WatcherService',
            'Paginator',
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
        DashboardService,
        $modal,
        WatcherService,
        Paginator,
    ) {
        var vm = this;

        vm.LEADWIRE_LOGIN_METHOD = CONFIG.LEADWIRE_LOGIN_METHOD;
        vm.selectedEnvironment = $localStorage.selectedEnvId.slice(0);
        vm.ownerTitle = "Owner Github :";
        var envName = "staging";

        vm.isAdmin = function (user) {
            var access = false;
            access = user.roles.indexOf("ROLE_SUPER_ADMIN") >= 0 || user.roles.indexOf("ROLE_ADMIN") >= 0;
            if(!access){
                if (vm.currentUser) {
                    Object.keys(vm.currentUser.acl).forEach(element => {
                        if (vm.currentUser.acl[element][vm.application.id].ACCESS === "ADMIN") {
                            access = true;
                        }
                    });
                }
            }

            return access;
        }

        vm.isEditor = function () {
            var access = false;
            if (vm.currentUser) {
                Object.keys(vm.currentUser.acl).forEach(element => {
                    if (vm.currentUser.acl[element][vm.application.id].ACCESS === "EDITOR") {
                        access = true;
                    }
                });
            }
            return access;
        }

        /**
         * get dashboards list
         */
        vm.getDashboardsList = function () {
            DashboardService.fetchDashboardsAllListByAppId(vm.application.id).then(function (dashboardsList) {
                vm.dashboardsNameList = Object.keys(dashboardsList['Default']);
                vm.defaultDashboardsList = dashboardsList['Default'];
                Object.keys(dashboardsList).forEach(function (k) {
                    Object.keys(dashboardsList[k]).forEach(function (key) {
                        dashboardsList[k][key].forEach(function (element) {
                            vm.dashboardsList.push({ ...element, key })
                        })
                    })
                })
            })
        }

        vm.getDashboardName = function (id) {
            var name = "-";
            vm.dashboardsList.forEach(element => {
                if (element.id === id)
                    name = element.key + " : " + element.name;
            });
            return name;
        }

        if (vm.LEADWIRE_LOGIN_METHOD === 'proxy' || vm.LEADWIRE_LOGIN_METHOD === 'login') {
            vm.ownerTitle = "Owner Login Id :"
        }

        vm.setWatcherLink = function () {
            WatcherService.list(vm.application.id, vm.selectedEnvironment)
                .then(function (data) {
                    vm.watchersList = data;
                }).catch(function (err) {
                    toastr.error(err.message || MESSAGES_CONSTANTS.ERROR);
                });
        };

        vm.editWatcher = function (watcher) {
            vm.addWatcher(watcher);
        }

        vm.deleteWatcher = function (id, index) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        WatcherService.delete(id, 
                            {'appId': vm.application.id, 
                             'envId': vm.selectedEnvironment})
                            .then(function () {
                                vm.watchersList.splice(index, 1);
                                toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                            }).catch(function (err) {
                                toastr.error(MESSAGES_CONSTANTS.ERROR);
                            })
                    } else {
                        swal.close();
                    }
                });
        }

        vm.executeWatcher = function (id) {
            WatcherService.execute(id,
                {'appId': vm.application.id, 
                'envId': vm.selectedEnvironment})
                .then(function () {
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                }).catch(function (err) {
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                })
        }

        vm.handleWatcher = function (watcher) {
            WatcherService.saveOrUpdate(watcher)
                .then(function () {
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                }).catch(function (err) {
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                })
        }

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

        vm.getReports = function () {

            envName = "staging";

            vm.environments.forEach(element => {
                if (element.id === vm.selectedEnvironment) {
                    envName = element.name;
                }
            });

            ApplicationService.getApplicationReports(vm.application.name, envName)
                .then(function (response) {
                    vm.paginator.items = vm.reportsList = response;
                }).catch(function (error) {
                    toastr.success(MESSAGES_CONSTANTS.ERROR);
                    vm.paginator.items = [];
                });

            vm.setWatcherLink();
        }

        vm.deleteReport = function (_id, _index, ind) {

            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        ApplicationService.deleteApplicationReport(_id, _index)
                            .then(function (response) {
                                vm.reportsList = vm.reportsList.filter((_, index) => index !== ind);
                                toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                            }).catch(function (error) {
                                toastr.success(MESSAGES_CONSTANTS.ERROR);
                            });
                    } else {
                        swal.close();
                    }
                });
        }

        vm.isErrorReport = function (msg) {
            if (msg.toLowerCase().indexOf("error") >= 0) {
                return true;
            }

            return false;
        }

        vm.getReportTitre = function (watcher) {
            var titre = "-";
            vm.watchersList.forEach(function (element) {
                if (element.title === watcher) {
                    titre = element.titre;
                }
            })
            return titre;
        }

        vm.getReportDashboard = function (watcher) {
            var dashboard = "-";
            vm.watchersList.forEach(function (element) {
                if (element.title === watcher) {
                    dashboard = vm.getDashboardName(element.dashboard);
                }
            })
            return dashboard;
        }

        vm.setAccess = function (user, access, level) {
            acl = {
                "user": user,
                "env": vm.selectedEnvironment,
                "app": vm.application.id,
                "level": level,
                "access": access
            };
            
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

        vm.getEnvironmentsForReport = function (user) {
            var isAdmin = user.roles.indexOf("ROLE_SUPER_ADMIN") >= 0 || user.roles.indexOf("ROLE_ADMIN") >= 0;
            if (vm.application && $rootScope.user.id === vm.application.owner.id || isAdmin) {
                return vm.environments;
            }

            var list = [];

            vm.environments.forEach(environment => {
                if (vm.currentUser && 
                    angular.isDefined(vm.currentUser.acl[environment.id][vm.application.id].ACCESS === "VIEWER") ||
                    angular.isDefined(vm.currentUser.acl[environment.id][vm.application.id].ACCESS === "EDITOR") ||
                    angular.isDefined(vm.currentUser.acl[environment.id][vm.application.id].ACCESS === "ADMIN")) {
                    list.push(environment);
                    vm.selectedEnvironment = environment.id;
                }
            });

            return list;
        }

        vm.getApp = function () {
            ApplicationFactory.get($stateParams.id)
                .then(function (res) {
                    vm.application = res.data;
                    vm.getDashboardsList();
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
                },vm.selectedEnvironment)
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
                        InvitationFactory.remove(id, 
                            {'appId': vm.application.id, 
                            'envId': vm.selectedEnvironment})
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
                                vm.ui.isDeleting = false;
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

        vm.addWatcher = function (watcher) {
            vm.modal = $modal.open({
                size: 'lg',
                templateUrl: 'application/watcher/add.html',
                controller: 'AddWatcherCtrl',
                controllerAs: 'ctrl'
            });

            vm.modal.appId = vm.application.id;
            vm.modal.envName = envName;
            vm.modal.appName = vm.application.name;
            vm.modal.envId = vm.selectedEnvironment;
            vm.modal.watcher = watcher;

            vm.modal.result.then(function () {
                vm.setWatcherLink();
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
                retention: CONFIG.LEADWIRE_STRIPE_ENABLED == true ? $rootScope.user.plan.retention : null,
                DOWNLOAD_URL: CONFIG.DOWNLOAD_URL,
                currentUser: null,
                watchersList: [],
                dashboardsList: [],
                paginator: Paginator.create({
                    itemsPerPage: 5,
                }),
            });
            vm.getEnvList();
            vm.getApp();
            vm.loadStats();
        };

        vm.getDashboardByTheme = function (name) {
            return vm.defaultDashboardsList[name];
        }

        vm.updateDashboardMenu = function () {
            ApplicationService.updateDashbaords(vm.application.id,vm.selectedEnvironment, 
                vm.defaultDashboardsList)
                .then(function () {
                    vm.flipActivityIndicator();
                    toastr.success(MESSAGES_CONSTANTS.EDIT_APP_SUCCESS);
                })
                .catch(function (error) {
                    vm.flipActivityIndicator();
                    toastr.error(
                        error.message ||
                        MESSAGES_CONSTANTS.EDIT_APP_FAILURE ||
                        MESSAGES_CONSTANTS.ERROR
                    );
                });
        }
    }
})(window.angular, window.swal, window.moment);
