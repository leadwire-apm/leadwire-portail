(function (angular) {
    angular
        .module('leadwireApp')
        .service('RouteGuard', function RouteGuardFN (
            $q, UserService, $location, $auth, $rootScope, $localStorage) {

                var service = this;

                service.skipIfLoggedIn = function () {
                    var deferred = $q.defer();
                    if ($auth.isAuthenticated()) {
                        deferred.reject();
                    } else {
                        deferred.resolve();
                    }
                    return deferred.promise;
                };

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
                    var roles = $localStorage.user && $localStorage.user.roles;
                    if (roles && $auth.isAuthenticated()) {
                        if (UserService.isAdmin($localStorage.user)) {
                            deferred.resolve();
                        } else {
                            deferred.reject('UNAUTHORIZED');
                            $location.path('/');
                        }
                    } else {
                        $location.path('/login');
                        deferred.reject();
                    }
                    return deferred.promise;
                };

                return service;
            },
        );

})(window.angular);
