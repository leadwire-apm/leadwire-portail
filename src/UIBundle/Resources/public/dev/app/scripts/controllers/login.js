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

LoginController.$inject = ['$location', '$auth', '$timeout', 'User'];

/**
 * LoginController : le controlleur de l'écran de l'authentification
 *
 * @param $location
 * @param $auth
 * @param $timeout
 * @param User
 * @constructor
 */
function LoginController($location, $auth, $timeout, User, $localStorage) {
    var vm = this;

    vm.authenticate = authenticate;

    (function initController() {
        // reset login status
        if (!$auth.isAuthenticated()) {
            return;
        }
        $auth.logout().then(function() {
            // toastr.info('You have been logged out');
            $location.path('/');
            $localStorage.user = null;
        });
    })();

    function authenticate(provider) {
        vm.dataLoading = true;
        $auth.authenticate(provider).then(function() {
            // toastr.success('You have successfully signed in with ' + provider + '!');
            User.getProfile();
            $timeout(function() {
                vm.dataLoading = false;
                $location.path('/');
            }, 200);
        }).catch(function(error) {
            vm.dataLoading = false;
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
