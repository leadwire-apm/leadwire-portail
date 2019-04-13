/**
 * Created by hamed on 02/06/17.
 */

(function() {
    'use strict';

    angular.module('leadwireApp').factory('ConfigService', ConfigService);

    ConfigService.$inject = ['$http', '$auth', '$localStorage', 'CONFIG'];

    function ConfigService($http, $auth, $localStorage, CONFIG) {
        var service = {};

        service.baseUrl = CONFIG.KIBANA_BASE_URL;

        service.getUrl = function(tenantPrefix, dashboardId, hasParameters) {
            var tenant = '';
            switch (tenantPrefix) {
                case 'app_':
                    tenant = $localStorage.selectedApp.sharedIndex;
                    break;
                case 'shared_':
                    tenant = $localStorage.selectedApp.sharedIndex;
                    break;
                case 'user_':
                    tenant = $localStorage.user.userIndex;
                    break;
                case 'all_user_':
                    tenant = $localStorage.user.allUserIndex;
                    break;
                default:
                    tenant = tenantPrefix;
            }

            return service.baseUrl + tenant + '?token=' + $auth.getToken() + '#/dashboard/' + dashboardId;
        };

        service.getDashboard = function(
            tenantPrefix, dashboardId, hasParameter) {
            var url = this.getUrl(tenantPrefix, '#/dashboard/' + dashboardId,
                hasParameter);
            return url;
        };

        service.setDashboard = function(tenant) {
            var url =
                service.baseUrl +
                tenant +
                '?token=' +
                $auth.getToken()
            console.log("config.js: " + url);
            return url;
        };

        return service;
    }
})();
