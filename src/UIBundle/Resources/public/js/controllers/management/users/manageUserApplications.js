(function (angular, swal) {
    angular.module('leadwireApp')
        .controller('ManageUserApplicationsController', [
            'UserService',
            'ApplicationService',
            'AccessLevelService',
            'EnvironmentService',
            'ApplicationFactory',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            '$sessionStorage',
            ManageUserApplicationsCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ManageUserApplicationsCtrlFN(
        UserService,
        ApplicationService,
        AccessLevelService,
        EnvironmentService,
        ApplicationFactory,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
        $sessionStorage
    ) {
        var vm = this;
        var userId = $state.params.id;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.getUser = function (id) {
            vm.flipActivityIndicator('isLoading');
            UserService.get(id)
                .then(function (user) {
                    vm.flipActivityIndicator('isLoading');
                    vm.user = user;
                })
                .catch(function (err) {
                    vm.flipActivityIndicator('isLoading');
                    vm.user = {};
                });
        };

        vm.loadApplications = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            ApplicationService.all()
                .then(function (applications) {
                    vm.flipActivityIndicator('isLoading');
                    vm.applications = applications;
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                    vm.applications = [];
                });
        }

        vm.loadUserApplications = function(id) {
            vm.flipActivityIndicator('isLoading');
            ApplicationFactory.findMyApplicationsById(id)
                .then(function (response) {
                    vm.userApplications = response.data;
                    vm.flipActivityIndicator('isLoading');
                })
                .catch(function (error) {
                    vm.userApplications = [];
                    vm.flipActivityIndicator('isLoading');
                });
        }

        vm.getEnvList = function () {
            vm.flipActivityIndicator('isLoading');

            EnvironmentService.list()
                .then(function (environments) {
                    vm.environments = environments;
                    vm.flipActivityIndicator('isLoading');
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                });
        }

        vm.hasAccess = function(application) {
            var access = false;
            if(vm.userApplications && vm.userApplications.length > 0){            
                vm.userApplications.map(app => {
                    if(app.id === application.id){
                        access = true;
                    }
                })
            }
            return access;
        }

        vm.getAclLevel = function (appId, value) {

            if (!vm.user.acl)
                return null;

            var checked = false;

            if (vm.user.acl[vm.selectedEnvironment] &&
                vm.user.acl[vm.selectedEnvironment][appId] && 
                vm.user.acl[vm.selectedEnvironment][appId]["ACCESS"] === value) {
                checked = true;
            }
            return checked;
        }

        vm.handleOnToggleLock = function(application, grant){
            vm.flipActivityIndicator('isLoading');
            if(grant){
                ApplicationService.grantUser(application.id, vm.user.id)
                .then(function (response) {
                    vm.flipActivityIndicator('isLoading');
                    vm.init();
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                    toastr.success(MESSAGES_CONSTANTS.ERROR);
                });
            } else {
                ApplicationService.revokePermission(application.id, vm.user.id)
                .then(function (response) {
                    vm.flipActivityIndicator('isLoading');
                    vm.init();
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                    toastr.success(MESSAGES_CONSTANTS.ERROR);
                });
            }

        }

        vm.setAccess = function (access, level, appId) {
            acl = {
                "user": vm.user.id,
                "env": vm.selectedEnvironment,
                "app": appId,
                "level": level,
                "access": access
            };

            vm.flipActivityIndicator('isLoading');
            
            AccessLevelService.setAccess(acl)
                .then(function (response) {
                    vm.user.acl = response.data.acls;
                    vm.flipActivityIndicator('isLoading');
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                    toastr.success(MESSAGES_CONSTANTS.ERROR);
                    vm.getApp();
                });

        }

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isLoading: false,
                },
                applications: [],
                user: {},
                selectedEnvironment: $sessionStorage.selectedEnvId.slice(0),
                environments: [],
                userApplications: null

            });
            vm.loadUserApplications(userId);
            vm.getUser(userId);
            vm.loadApplications();
            vm.getEnvList();
        };
    }
}

)(window.angular, window.swal);
