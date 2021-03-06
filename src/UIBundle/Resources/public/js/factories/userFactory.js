(function (angular) {
    angular.module('leadwireApp').
        factory('UserFactory', ['$http', 'CONFIG', UserFactoryFN]);

    function UserFactoryFN ($http, CONFIG) {
        return {
            list: function () {
                return $http.get(CONFIG.BASE_URL + 'api/user/list');
            },
            listACLManagement: function () {
                return $http.get(CONFIG.BASE_URL + 'api/user/acl/list/management');
            },
            delete: function (id) {
                return $http.delete(
                    CONFIG.BASE_URL + 'api/user/' + id + '/delete');
            },
            get: function (id) {
                return $http.get(CONFIG.BASE_URL + 'api/user/' + id + '/get');
            },
            getProfile: function () {
                return $http.get(CONFIG.BASE_URL + 'api/user/me');
            },
            update: function (profileData) {
                return $http.put(
                    CONFIG.BASE_URL + 'api/user/' + profileData.id + '/update',
                    profileData,
                );
            },
            subscribe: function (body, userId) {
                return $http.post(
                    CONFIG.BASE_URL + 'api/user/' + userId + '/subscribe',
                    body,
                );
            },
            invoices: function (userId) {
                return $http.get(
                    CONFIG.BASE_URL + 'api/user/' + userId + '/invoices',
                );
            },
            subscription: function (userId) {
                return $http.get(
                    CONFIG.BASE_URL + 'api/user/' + userId + '/subscription',
                );
            },
            updateSubscription: function (body, userId) {
                return $http.put(
                    CONFIG.BASE_URL + 'api/user/' + userId + '/subscribe',
                    body,
                );
            },
            editPaymentMethod: function (cardInfo, userId) {
                return $http.put(
                    CONFIG.BASE_URL + 'api/user/' + userId + '/creditCard',
                    cardInfo,
                );
            },
            toggleStatus: function (id, body) {
                return $http.put(
                    CONFIG.BASE_URL + 'api/user/' + id + '/lock-toggle',
                    body,
                );
            },
            getProxyHeaders: function(){
                return $http.get( CONFIG.BASE_URL
                );
            },
            grantAccess: function(payload) {
                return $http.put(
                    CONFIG.BASE_URL + 'api/user/access-level/grant',
                    payload
                );
            },
            revokeAccess: function(payload) {
                return $http.put(
                    CONFIG.BASE_URL + 'api/user/access-level/revoke',
                    payload
                );
            }
        };
    }
})(window.angular);
