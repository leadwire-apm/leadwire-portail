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
            'socket',
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
        $scope,
        socket
    ) {
        var vm = this;

        socket.on('heavy-operation', function(data) {

            if (data.status == "in-progress") {
                if ($('#toast-container').hasClass('toast-top-right') == false) {
                    toastr.info(
                        data.message + '...',
                        "Operation in progress",
                        {
                            timeOut: 0,
                            extendedTimeOut: 0,
                            closeButton: true,
                            onClick: null,
                            preventDuplicates: true
                        }
                    );
                } else {
                    $('.toast-message').html(data.message + '...');
                }
            }
            if (data.status == "done") {
                toastr.clear();
            }
        });

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.loadEnvironments = function () {
            vm.flipActivityIndicator('isLoading');
            vm.selectedEnvironment = null;
            vm.selectedEnvironmentName = null;
            vm.selectedApplication = 'all';
            vm.selectedApplicationName = null;
            vm.reset();
            // should send some criteria
            EnvironmentService.findAllMinimalist()
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
            vm.selectedApplication = 'all';
            vm.selectedApplicationName = null;
            vm.reset();
            // should send some criteria
            ApplicationService.allMinimalist()
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
            console.log(idApplication);
            vm.flipActivityIndicator('isLoading');
            vm.selectedApplication = 'all';
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
                    vm.selectedApplication = idApplication != null ? idApplication : 'all';
                    if (idApplication == null) {
                        vm.selectedApplicationName = 'all';
                    } else {
                        ApplicationService.findMinimalist(idApplication).then(function(application) {
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
                selectedApplication: 'all',
                selectedApplicationName: null,
            });
            vm.loadEnvironments();
        };
    }
})(window.angular);
