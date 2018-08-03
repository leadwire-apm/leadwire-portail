/**
 * Created by hamed on 02/06/17.
 */

angular.module('leadwireApp').
    controller('LoginCtrl', LoginController).
    controller('logoutCtrl', logout);

function logout($location, $localStorage) {
    $localStorage.$reset();
    $location.path('/login');
}

//
// LoginController.$inject = [
//     '$location',
//     '$auth',
//     '$timeout',
//     'User',
//     '$localStorage'];

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
    $location, $auth, $timeout, UserService, Invitation, $localStorage,
    toastr, MESSAGES_CONSTANTS) {
    var vm = this;
    initController();
    vm.authenticate = authenticate;
    var invitationId = $location.$$search && $location.$$search.invitation ?
        $location.$$search.invitation : undefined;

    function authenticate(provider) {
        vm.isChecking = true;
        $auth.authenticate(provider).then(function() {
            UserService.handleOnSuccessLogin(invitationId).then(function() {
                toastr.success(MESSAGES_CONSTANTS.LOGIN_SUCCESS(provider));
                vm.isChecking = false;
                $location.search({});
                $location.path('/');

            });
        }).catch(function(error) {
            handleLoginFailure(error);
        });
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

    function initController() {
        // reset login status
        if (!$auth.isAuthenticated()) {
            return;
        }
        delete $localStorage.user;
        $auth.logout().then(function() {
            toastr.info(MESSAGES_CONSTANTS.LOGOUT_SUCCESS);
            $location.path('/login');
        });
    }
}
