(function (angular) {
    angular.module('leadwireApp').
        factory('AccessLevelFactory', ['$http', 'CONFIG', AccessLevelFactoryFN]);

    function AccessLevelFactoryFN($http, CONFIG) {
        return {
            setAccess: function (acl) {
                return $http.put(
                    CONFIG.BASE_URL + 'api/access-level/update',
                    acl
                );
            },
            deleteAccess: function (acl) {
                return $http.delete(
                    CONFIG.BASE_URL + 'api/access-level/delete',
                    acl
                );
            }
        };
    }
})(window.angular);
