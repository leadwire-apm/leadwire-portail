/**
 * Created by hamed on 02/06/17.
 */

angular
    .module('leadwireApp')
    .controller('LoginCtrl', LoginController)
    .controller('logoutCtrl', logout);

function logout($location, $localStorage) {
    $localStorage.$reset();
    $location.path('/login');
}

/**
 * LoginController : le controlleur de l'écran de l'authentification
 *
 * @param $location
 * @param $auth
 * @param $timeout
 * @param UserService
 * @param Invitation
 * @param $localStorage
 * @param toastr
 * @param MESSAGES_CONSTANTS
 * @constructor
 */
function LoginController(
    $location,
    $auth,
    $timeout,
    UserService,
    Invitation,
    $localStorage,
    toastr,
    MESSAGES_CONSTANTS,
    DashboardService
) {
    var vm = this;
    initController();
    vm.authenticate = authenticate;
    var invitationId =
        $location.$$search && $location.$$search.invitation
            ? $location.$$search.invitation
            : undefined;

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
            vm.isChecking = false;
            $location.search({});
            $location.path('/' + dashboardId !== null ? dashboardId : '');
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
        if (user.defaultApp && user.defaultApp.id) {
            return DashboardService.fetchDashboardsByAppId(user.defaultApp.id);
        } else {
            return null;
        }
    }

    function initController() {
        if ($auth.isAuthenticated()) {
            $location.path('/');
        }
    }
}
