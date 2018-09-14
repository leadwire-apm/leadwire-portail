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
                .then(UserService.handleBeforeRedirect) //accept invitation and update Localstorage
                .then(handleAfterRedirect) //fetch application and dashboard
                .then(handleLoginSuccess(provider)) //redirect
                .catch(handleLoginFailure);
        }

        function handleLoginSuccess(provider) {
            return function(response) {
                toastr.success(MESSAGES_CONSTANTS.LOGIN_SUCCESS(provider));
                //clear query string (?invitationId=***)
                $location.search({});
                vm.isChecking = false;
                if (
                    response &&
                    response.dashboards &&
                    response.dashboards &&
                    response.dashboards.length
                ) {
                    //redirect to first dashboard
                    $state.go('app.dashboard.home', {
                        id: response.dashboards[0].id
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
           return ApplicationFactory.findAll().then(function(response) {
                if (response.data && response.data.length) {
                    $rootScope.$broadcast('set:apps', response.data);
                }
               if (
                   user.defaultApp &&
                   user.defaultApp.id &&
                   user.defaultApp.isEnabled
               ) {
                   //take the default app
                   return DashboardService.fetchDashboardsByAppId(
                       user.defaultApp.id
                   );
               } else {
                   return null;
                   // else take the first enabled app
                   // return ApplicationFactory.findAll()
                   //     .then(function(response) {
                   //         if (response.data && response.data.length) {
                   //             $rootScope.$broadcast('set:apps', response.data);
                   //             var firstEnabled = response.data.find(function(
                   //                 app
                   //             ) {
                   //                 return app.isEnabled;
                   //             });
                   //             if (firstEnabled) {
                   //                 return DashboardService.fetchDashboardsByAppId(
                   //                     firstEnabled.id
                   //                 );
                   //             } else {
                   //                 return null;
                   //             }
                   //         }
                   //         return null;
                   //     })
                   //     .catch(function(error) {
                   //         console.log('HandleAfterRedirect', error);
                   //         return null;
                   //     });
                   // no default app
               }
            });


        }

        function onLoad() {
            if ($auth.isAuthenticated()) {
                if (invitationId !== undefined && $localStorage.user) {
                    InvitationService.acceptInvitation(
                        invitationId,
                        $localStorage.user.id
                    )
                        .then(function(app) {
                            toastr.success(
                                MESSAGES_CONSTANTS.INVITATION_ACCEPTED
                            );
                            (
                                $localStorage.applications ||
                                ($localStorage.applications = [])
                            ).push(app);
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
