(function (angular) {
    angular.module('leadwireApp')
        .controller('AccessLevelController', [
            'EnvironmentService',
            'ApplicationService',
            'AccessLevelService',
            'UserService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            '$scope',
            AccessLevelControllerFN
        ]);

    /**
     * Handle Access Level logic
     *
     */
    function AccessLevelControllerFN (
        EnvironmentService,
        ApplicationService,
        AccessLevelService,
        UserService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
        $scope
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.loadEnvironments = function () {
            vm.flipActivityIndicator('isLoading');
            vm.selectedEnvironment = null;
            vm.selectedEnvironmentName = null;
            vm.selectedApplication = null;
            vm.selectedApplicationName = null;
            vm.reset();
            // should send some criteria
            EnvironmentService.list()
                .then(function (environments) {
                    vm.flipActivityIndicator('isLoading');
                    vm.environments = environments;
                    vm.view.environments = true;
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                });
        };

        vm.loadApplications = function (idEnvironment) {
            vm.flipActivityIndicator('isLoading');
            vm.selectedApplication = null;
            vm.selectedApplicationName = null;
            vm.reset();
            // should send some criteria
            ApplicationService.all()
                .then(function (applications) {
                    vm.flipActivityIndicator('isLoading');
                    vm.applications = applications;
                    vm.view.applications = true;
                    vm.selectedEnvironment = idEnvironment;
                    EnvironmentService.findMinimalist(idEnvironment).then(function(environment) {
                        vm.selectedEnvironmentName = environment.name;
                    }).catch(function(error) {
                        vm.flipActivityIndicator('isLoading');
                    });
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                });
        };

        vm.loadUsers = function (idEnvironment, idApplication) {
            vm.flipActivityIndicator('isLoading');
            vm.selectedApplication = null;
            vm.selectedApplicationName = null;
            vm.reset();
            // should send some criteria
            UserService.listACLManagement()
                .then(function (users) {
                    vm.flipActivityIndicator('isLoading');
                    vm.users = users;
                    vm.view.users = true;
                    vm.selectedEnvironment = idEnvironment;
                    EnvironmentService.findMinimalist(idEnvironment).then(function(environment) {
                        vm.selectedEnvironmentName = environment.name;
                    }).catch(function(error) {
                        vm.flipActivityIndicator('isLoading');
                    });
                    vm.selectedApplication = idApplication;
                    if (idApplication == null) {
                        vm.selectedApplicationName = 'all';
                    } else {
                        ApplicationService.find(idApplication).then(function(application) {
                            vm.selectedApplicationName = application.name;
                        }).catch(function(error) {
                            vm.flipActivityIndicator('isLoading');
                        });
                    }
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                });
        };

        vm.setAccess = function(user, env, app, level, access) {
            acl = {
                "user": user,
                "env": env,
                "app": app,
                "level": level,
                "access": access
            };
            AccessLevelService.setAccess(acl)
                .then(function(response) {
                    var user = response.data;
                    var index = _.findIndex(vm.users, {id: user.id});
                    vm.users.splice(index, 1, user);
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                })
            ;

            console.log(acl);
        }

        vm.reset = function() {
            vm.environments = [];
            vm.applications = [];
            vm.users = [];
            vm.view.environments = false;
            vm.view.applications = false;
            vm.view.users = false;
        }

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                environments: [],
                applications: [],
                user: [],
                view: {
                    'environments': false,
                    'applications': false,
                    'users': false,
                },
                selectedEnvironment: null,
                selectedEnvironmentName: null,
                selectedApplication: null,
                selectedApplicationName: null,
            });
            vm.loadEnvironments();
        };
    }
})(window.angular);
