/**
 * Created by hamed on 02/06/17.
 */

angular.module('leadwireApp').controller('LoginCtrl', LoginController);

LoginController.$inject = ['$location', '$auth', '$timeout', 'User'];

/**
 * LoginController : le controlleur de l'écran de l'authentification
 * @param $location
 * @constructor
 */
function LoginController($location, $auth, $timeout, User) {
    var ctrl = this;

    ctrl.login = login;
    ctrl.authenticate = authenticate;

    (function initController() {
        // reset login status
        if (!$auth.isAuthenticated()) { return; }
        $auth.logout()
            .then(function () {
                // toastr.info('You have been logged out');
                $location.path('/');
            });
    })();

    function login() {

    };

    function authenticate(provider) {
        ctrl.dataLoading = true;
        $auth.authenticate(provider)
            .then(function () {
                // toastr.success('You have successfully signed in with ' + provider + '!');
                User.getProfile();
                $timeout(function () {
                    ctrl.dataLoading = false;
                    $location.path('/');
                }, 200);
            })
            .catch(function (error) {
                ctrl.dataLoading = false;
                if (error.message) {
                    // toastr.error(error.message);
                } else if (error.data) {
                    // toastr.error(error.data.message, error.status);
                } else {
                    //toastr.error(error);
                }
            });
    };
}
