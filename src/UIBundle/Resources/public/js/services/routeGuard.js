(function (angular) {
    angular
        .module('leadwireApp')
        .service('RouteGuard', [
            '$q',
            'UserService',
            '$location',
            '$auth',
            '$localStorage',
            RouteGuardFN,
        ]);

    function RouteGuardFN ($q, UserService, $location, $auth, $localStorage) {

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
                    && UserService.isAdmin($localStorage.user)) {
                    deferred.resolve();
                } else {
                    $location.path('/');
                }
            } else {
                $location.path('/login');
            }
            return deferred.promise;
        };

        return service;
    }

})(window.angular);