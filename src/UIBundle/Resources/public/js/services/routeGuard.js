(function (angular) {
    angular
        .module('leadwireApp')
        .service('RouteGuard', [
            '$q',
            'UserService',
            '$location',
            '$auth',
            '$rootScope',
            '$localStorage',
            RouteGuardFN,
        ]);

    function RouteGuardFN (
        $q, UserService, $location, $auth, $rootScope, $localStorage) {

        var service = this;
        service.loginRequired = function () {
            var deferred = $q.defer();
            if ($auth.isAuthenticated()) {
                $rootScope.menus = MenuFactory.get('SETTINGS');
                deferred.resolve();
            } else {
                $location.path('/login');
            }
            return deferred.promise;
        };

        service.adminRequired = function () {
            var deferred = $q.defer();
            var roles = $localStorage.user.roles;
            if ($auth.isAuthenticated()) {
                if (roles && (UserService.isAdmin($localStorage.user)
                )) {
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
    }

})(window.angular);