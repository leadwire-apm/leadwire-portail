/**
 * LoginControllerFN : le controlleur de l'écran de l'authentification
 *
 * @param $location
 * @param $auth
 * @param InvitationService
 * @param UserService
 * @param $localStorage
 * @param MenuFactory
 * @param toastr
 * @param MESSAGES_CONSTANTS
 * @param DashboardService
 * @param ApplicationFactory
 * @param $rootScope
 * @param $state
 * @param CONFIG
 * @constructor
 */
 function LoginControllerFN(
        $location,
        $auth,
        InvitationService,
        UserService,
        MenuFactory,
        $localStorage,
        toastr,
        MESSAGES_CONSTANTS,
        DashboardService,
        ApplicationFactory,
        $rootScope,
        $state,
        CONFIG,
        $http,
        rx,
        $timeout
    ) {
        var vm = this;
        vm.invitationId =
            $location.$$search && $location.$$search.invitation
            ? $location.$$search.invitation
            : undefined
        ;
        onLoad();

        vm.authenticate = authenticate;

        vm.loginMethod = CONFIG.LOGIN_METHOD;
        vm.COMPAGNE_ENABLED = CONFIG.COMPAGNE_ENABLED;

        if (vm.loginMethod === "proxy") {
            proxyAuthenticate(vm.loginMethod);
    }

    function authenticate() {
        if (vm.loginMethod === "github") {
            providerAuthenticate(vm.loginMethod);
        } else if (vm.loginMethod === "login") {
            loginAuthenticate(vm.loginMethod);
        } else if (vm.loginMethod === "proxy") {
            proxyAuthenticate(vm.loginMethod);
        }
    }

    function providerAuthenticate(provider) {
        vm.isChecking = true;
        $timeout(loginProcess, 1800);
        $auth
            .authenticate(provider)
            .then(getMe) // accept invitation and update Localstorage
            .then(handleAfterRedirect) // fetch application and dashboard
            .then(handleLoginSuccess(provider)) // redirect
            .catch(handleLoginFailure)
        ;
    }

    function loginAuthenticate(provider) {
        if (!vm.login || !vm.password) {
            toastr.error(MESSAGES_CONSTANTS.LOGIN_REQUIRED);
            return;
        }
        vm.isChecking = true;
        $timeout(loginProcess, 1800);
        $auth
          .login({ username: vm.login })
          .then(getMe) // accept invitation and update Localstorage
          .then(handleAfterRedirect) // fetch application and dashboard
          .then(handleLoginSuccess(provider)) // redirect
          .catch(handleLoginFailure)
        ;
    }

    function proxyAuthenticate(provider) {

        UserService.getProxyHeaders(function(headers) {
            vm.isChecking = true;
            $timeout(loginProcess, 1800);
            $auth
              .login()
              .then(getMe) // accept invitation and update Localstorage
              .then(handleAfterRedirect) // fetch application and dashboard
              .then(handleLoginSuccess(provider)) // redirect
              .catch(handleLoginFailure)
            ;
        });
    }

    function getMe() {
        return UserService.handleBeforeRedirect(vm.invitationId);
    }

    var loginProcess = function () {
        $rootScope.checkLoginProcess();
    }

    function handleLoginSuccess(provider) {
        return function(response) {
            toastr.clear();
            toastr.success(MESSAGES_CONSTANTS.LOGIN_SUCCESS(provider));
            // clear query string (?invitationId=***)
            $location.search({});
            vm.isChecking = false;
            if (response && response.dashboards && response.dashboards.length) {
                //redirect to first dashboard
                $state.go(response.path, {
                    id: response.dashboards[0].id,
                    tenant: null
                });
            } else {
                $state.go(response.path);
            }
            return true;
        };
    }

    function handleLoginFailure(error) {
        vm.isChecking = false;
        toastr.clear();
        var message = null;
        if (error.message) {
            message = error.message;
        } else if (error.data && error.data.message) {
            message = error.data.message;
        } else if (error.data && error.data.error) {
            if (error.data.error.exception && error.data.error.exception.length) {
                message = error.data.error.exception[0].message;
            } else {
                message = error.data.error.message;
            }
        } else {
            message = error;
        }
        toastr.error(message);
    }

    function handleAfterRedirect(user) {
        const isAdmin = UserService.isAdmin(user);
        const isSuperAdmin =
        user.roles.indexOf(UserService.getRoles().SUPER_ADMIN) !== -1;
        if (isAdmin || isSuperAdmin) {
            $localStorage.currentMenu = MenuFactory.get("MANAGEMENT");
            return { path: "app.management.applications" };
        } else {
            // Simple user
            return ApplicationFactory.findMyApplications().then(function(response) {
                if (response.data && response.data.length) {
                    $rootScope.$broadcast("set:apps", response.data);
                }
                if (user.defaultApp && user.defaultApp.id && user.defaultApp.enabled) {
                    //take the default app
                    return DashboardService.fetchDashboardsByAppId(user.defaultApp.id);
                } else {
                    return { path: "app.applicationsList" };
                }
            });
        }
    }

    function onLoad() {
        if ($auth.isAuthenticated()) {
            if ($localStorage.user) {

                if (vm.invitationId !== undefined) {
                    InvitationService.acceptInvitation(
                        vm.invitationId,
                        $localStorage.user.id
                        )
                    .then(function(app) {
                        toastr.success(MESSAGES_CONSTANTS.INVITATION_ACCEPTED);
                        (
                            $localStorage.applications || ($localStorage.applications = [])
                            ).push(app);
                        $state.go("app.applicationsList");
                    })
                    .catch(function(error) {
                        toastr.error(MESSAGES_CONSTANTS.ERROR);
                        console.log("onLoad Login", error);
                    });
                } else {
                    $state.go("app.applicationsList");
                }
            } else {
                $state.go("login");
            }
        }
    }

    function checkLoginProcess () {
        var deferred = $http({
            url: CONFIG.BASE_URL + 'api/process/login/get',
            method: "get"
        });

        return rx.Observable
            .fromPromise(deferred)
            .retry(10)
            .map(function(response) {
                return response.data;
            });
    }

    $rootScope.$createObservableFunction ('checkLoginProcess')
        .debounce(250)
        .flatMapLatest(checkLoginProcess)
        .subscribe(
            function(result) {
                if (result != null && result.status == "in-progress") {
                    if ($('#toast-container').hasClass('.toast-top-right') == false) {
                        toastr.info(
                            result.message + '...',
                            "Opération in progress",
                            {
                                timeOut: 0,
                                extendedTimeOut: 0,
                                closeButton: false,
                                onClick: null,
                                preventDuplicates: true,
                                progressBar: false
                            }
                        );
                    } else {
                        $('.toast-message').html(result.message + '...');
                    }
                    $rootScope.checkLoginProcess();
                } else {
                    toastr.clear();
                }
            }
        )
    ;
}

(function(angular) {
    angular
    .module("leadwireApp")
    .controller("LoginCtrl", [
        "$location",
        "$auth",
        "InvitationService",
        "UserService",
        "MenuFactory",
        "$localStorage",
        "toastr",
        "MESSAGES_CONSTANTS",
        "DashboardService",
        "ApplicationFactory",
        "$rootScope",
        "$state",
        "CONFIG",
        "$http",
        "rx",
        "$timeout",
        LoginControllerFN
        ]);
})(window.angular);
