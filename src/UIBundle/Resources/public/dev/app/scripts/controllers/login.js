(function(angular) {
    angular
        .module('leadwireApp')
        .controller('LoginCtrl', [
            '$location',
            '$auth',
            'InvitationService',
            'UserService',
            '$localStorage',
            'toastr',
            'MESSAGES_CONSTANTS',
            'DashboardService',
            'ApplicationFactory',
            '$rootScope',
            '$state',
            LoginControllerFN
        ]);

    /**
     * LoginControllerFN : le controlleur de l'écran de l'authentification
     *
     * @param $location
     * @param $auth
     * @param InvitationService
     * @param UserService
     * @param $localStorage
     * @param toastr
     * @param MESSAGES_CONSTANTS
     * @param DashboardService
     * @param ApplicationFactory
     * @param $rootScope
     * @param $state
     * @constructor
     */
    function LoginControllerFN(
        $location,
        $auth,
        InvitationService,
        UserService,
        $localStorage,
        toastr,
        MESSAGES_CONSTANTS,
        DashboardService,
        ApplicationFactory,
        $rootScope,
        $state
    ) {
        var vm = this;
        var invitationId =
            $location.$$search && $location.$$search.invitation
                ? $location.$$search.invitation
                : undefined;
        onLoad();
        vm.authenticate = authenticate;

        function authenticate(provider) {
            vm.isChecking = true;

            $auth
                .authenticate(provider)
                .then(function() {
                    return invitationId;
                })
                .then(UserService.handleBeforeRedirect)
                .then(handleAfterRedirect)
                .then(handleLoginSuccess(provider))
                .catch(handleLoginFailure);
        }

        function handleLoginSuccess(provider) {
            return function(dashboardId) {
                toastr.success(MESSAGES_CONSTANTS.LOGIN_SUCCESS(provider));
                $location.search({});
                vm.isChecking = false;
                if (dashboardId !== null) {
                    $state.go('app.dashboard.home', {
                        id: dashboardId
                    });
                } else {
                    $state.go('app.applicationsList');
                }
                return true;
            };
        }

        function handleLoginFailure(error) {
            vm.isChecking = false;
            var message = null;
            if (error.message) {
                message = error.message;
            } else if (error.data) {
                message = error.data.message;
            } else {
                message = error;
            }
            toastr.error(message);
        }

        function handleAfterRedirect(user) {
            if (
                user.defaultApp &&
                user.defaultApp.id &&
                user.defaultApp.isEnabled
            ) {
                ApplicationFactory.findAll().then(function(response) {
                    if (response.data && response.data.length) {
                        $rootScope.$broadcast('set:apps', response.data);
                    }
                });
                //take the default app
                return DashboardService.fetchDashboardsByAppId(
                    user.defaultApp.id
                );
            } else {
                // else take the first enabled app
                return ApplicationFactory.findAll()
                    .then(function(response) {
                        if (response.data && response.data.length) {
                            $rootScope.$broadcast('set:apps', response.data);
                            var firstEnabled = response.data.find(function(
                                app
                            ) {
                                return app.isEnabled;
                            });
                            if (firstEnabled) {
                                return DashboardService.fetchDashboardsByAppId(
                                    firstEnabled.id
                                );
                            } else {
                                return null;
                            }
                        }
                        return null;
                    })
                    .catch(function(error) {
                        console.log('HandleAfterRedirect', error);
                        return null;
                    });
                // no default app
            }
        }

        function onLoad() {
            if ($auth.isAuthenticated()) {
                console.log('Connected User', $localStorage.user);
                if (invitationId !== undefined && $localStorage.user) {
                    InvitationService.acceptInvitation(
                        invitationId,
                        $localStorage.user.id
                    )
                        .then(function() {
                            toastr.success(
                                MESSAGES_CONSTANTS.INVITATION_ACCEPTED
                            );
                            $state.go('app.applicationsList');
                        })
                        .catch(function(error) {
                            toastr.error(MESSAGES_CONSTANTS.ERROR);
                            console.log('onLoad Login', error);
                        });
                } else {
                    $state.go('app.applicationsList');
                }
            }
        }
    }
})(window.angular);
