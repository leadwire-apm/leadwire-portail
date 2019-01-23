(function (angular) {
    angular
        .module('leadwireApp')
        .service('RouteGuard', [
            '$q',
            '$location',
            '$auth',
            '$localStorage',
            RouteGuardFN
        ]);

    function RouteGuardFN($q, $location, $auth, $localStorage) {

        var service = this;
        service.loginRequired = function () {
            var deferred = $q.defer();
            if ($auth.isAuthenticated()) {
                deferred.resolve();
            } else {
                $location.path('/login');
            }
            return deferred.promise;
        };

        service.adminRequired = function () {
            var deferred = $q.defer();
            if ($auth.isAuthenticated()) {
                if ($localStorage.user.roles
                    && $localStorage.user.roles.indexOf('ROLE_ADMIN') !== -1) {
                    deferred.resolve();
                } else {
                    $location.path('/');
                }
            } else {
                $location.path('/login');
            }
            return deferred.promise;
        }

        return service
    }

})(window.angular);