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
  CONFIG
) {
  var vm = this;
  vm.invitationId =
    $location.$$search && $location.$$search.invitation
      ? $location.$$search.invitation
      : undefined;
  onLoad();

  vm.authenticate = authenticate;

  vm.loginMethod = CONFIG.LOGIN_METHOD;

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
    $auth
      .authenticate(provider)
      .then(getMe) // accept invitation and update Localstorage
      .then(handleAfterRedirect) // fetch application and dashboard
      .then(handleLoginSuccess(provider)) // redirect
      .catch(handleLoginFailure);
  }

  function loginAuthenticate(provider) {
    if (!vm.login || !vm.password) {
      toastr.error(MESSAGES_CONSTANTS.LOGIN_REQUIRED);
      return;
    }

    vm.isChecking = true;

    $auth
      .login({ username: vm.login })
      .then(getMe) // accept invitation and update Localstorage
      .then(handleAfterRedirect) // fetch application and dashboard
      .then(handleLoginSuccess(provider)) // redirect
      .catch(handleLoginFailure);
  }

  function proxyAuthenticate(provider) {
    var userInfos = {};

    UserService.getProxyHeaders(function(headers) {
      if (
        angular.isUndefined(headers.username) ||
        angular.isUndefined(headers.group) ||
        angular.isUndefined(headers.email) ||
        !headers.username ||
        !headers.group ||
        !headers.email
      ) {
        toastr.error(MESSAGES_CONSTANTS.PROXY_HEADER_REQUIRED);
        return;
      }

      vm.isChecking = true;

      userInfos.group = headers.group;
      userInfos.username = headers.username;
      userInfos.email = headers.email;

      $auth
        .login(userInfos)
        .then(getMe) // accept invitation and update Localstorage
        .then(handleAfterRedirect) // fetch application and dashboard
        .then(handleLoginSuccess(provider)) // redirect
        .catch(handleLoginFailure);
    });
  }
  function getMe() {
    return UserService.handleBeforeRedirect(vm.invitationId);
  }

  function handleLoginSuccess(provider) {
    return function(response) {
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
      LoginControllerFN
    ]);
})(window.angular);
