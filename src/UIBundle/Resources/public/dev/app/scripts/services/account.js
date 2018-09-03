(function(angular) {
    angular
        .module('leadwireApp')
        .factory('Account', ['$http', 'CONFIG', UserFactoryFN]);

    function UserFactoryFN($http, CONFIG) {
        return {
            getProfile: function() {
                return $http.get(CONFIG.BASE_URL + 'api/user/me');
            },
            updateProfile: function(profileData) {
                return $http.put(
                    CONFIG.BASE_URL + 'api/user/' + profileData.id + '/update',
                    profileData
                );
            }
        };
    }
})(window.angular);
