/**
 * Created by hamed on 02/06/17.
 */

(function () {
    'use strict';

    angular.module('leadwireApp').factory('ConfigService', ConfigService);

    ConfigService.$inject = ['$http', '$auth', '$sessionStorage', 'CONFIG'];

    function ConfigService($http, $auth, $sessionStorage, CONFIG) {
        var service = {};

        service.baseUrl = CONFIG.KIBANA_BASE_URL;

        service.getUrl = function (tenantPrefix, dashboardId, hasParameters) {
            var tenant = '';
            switch (tenantPrefix) {
                case 'app_':
                    tenant = $sessionStorage.selectedApp.sharedIndex;
                    break;
                case 'shared_':
                    tenant = $sessionStorage.selectedApp.sharedIndex;
                    break;
                case 'user_':
                    tenant = $sessionStorage.user.userIndex;
                    break;
                case 'all_user_':
                    tenant = $sessionStorage.user.allUserIndex;
                    break;
                default:
                    tenant = tenantPrefix;
            }

            return service.baseUrl + tenant + '?token=' + $auth.getToken() + '#/dashboard/' + dashboardId;
        };

        service.getDashboard = function (tenantPrefix, dashboardId, hasParameter) {
            var url = this.getUrl(tenantPrefix, '#/dashboard/' + dashboardId, hasParameter);
            return url;
        };

        service.setDashboard = function (params) {

            var tenant = $sessionStorage.user.username;

            if (params === "shared") {
                tenant = $sessionStorage.selectedEnv.name + "-" + $sessionStorage.selectedApp.sharedIndex;
            }

            var url =
                service.baseUrl +
                "app/dashboards?security_tenant=" + tenant + "#/create?_g=(filters:!(),refreshInterval:(pause:!t,value:0),time:(from:now-15m,to:now))&_a=(description:'',filters:!(),fullScreenMode:!f,options:(hidePanelTitles:!f,useMargins:!t),panels:!(),query:(language:kuery,query:''),timeRestore:!f,title:'',viewMode:edit)";
            return url;
        };
        return service;
    }
})();
