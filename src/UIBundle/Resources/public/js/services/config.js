/**
 * Created by hamed on 02/06/17.
 */

(function () {
    'use strict';

    angular.module('leadwireApp').factory('ConfigService', ConfigService);

    ConfigService.$inject = ['$http', '$auth', '$localStorage', 'CONFIG'];

    function ConfigService($http, $auth, $localStorage, CONFIG) {
        var service = {};

        service.baseUrl = CONFIG.KIBANA_BASE_URL;

        service.getUrl = function (tenantPrefix, dashboardId, hasParameters) {
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

        service.getDashboard = function (tenantPrefix, dashboardId, hasParameter) {
            var url = this.getUrl(tenantPrefix, '#/dashboard/' + dashboardId,
                hasParameter);
            return url;
        };

        service.setDashboard = function (params) {

            var tenant = "Private";

            if (params === "shared") {
                tenant = $localStorage.selectedEnv.name + "-" + $localStorage.selectedApp.sharedIndex;
            }

            var url =
                service.baseUrl +
                'app/kibana?security_tenant=' +
                tenant +
                '#/dashboard?_g=()&_a=( description:\'\',filters:!(), fullScreenMode:!f,options:( darkTheme:!f,hidePanelTitles:! f,useMargins:!t),panels:!(), query:(language:lucene,query:\' \'),timeRestore:!f,title:\'\',viewMode:edit)';
            return url;
        };
        return service;
    }
})();
