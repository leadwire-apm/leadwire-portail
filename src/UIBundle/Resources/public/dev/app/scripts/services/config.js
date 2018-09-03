/**
 * Created by hamed on 02/06/17.
 */

(function() {
    'use strict';

    angular.module('leadwireApp').factory('ConfigService', ConfigService);

    ConfigService.$inject = ['$http', '$auth', '$localStorage'];

    function ConfigService($http, $auth, $localStorage) {
        var service = {};

        service.baseUrl = 'https://kibana.leadwire.io/';

        service.getUrl = function(dashboardId, hasParameters) {
            if (hasParameters === true) {
                return (
                    service.baseUrl +
                    $localStorage.user.uuid +
                    '&token=' +
                    $auth.getToken() +
                    dashboardId
                );
            } else {
                return (
                    service.baseUrl +
                    $localStorage.user.uuid +
                    '?token=' +
                    $auth.getToken() +
                    dashboardId
                );
            }
        };

        service.getDashboard = function(dashboardId, hasParameter) {
            var url = this.getUrl('#/dashboard/' + dashboardId, hasParameter);
            return url;
        };



        service.setDashboard = function(tenant) {
            var url =
                service.baseUrl +
                tenant +
                '?token=' +
                $auth.getToken() +
                '#/dashboard?_g=()&_a=( description:\'\',filters:!(), fullScreenMode:!f,options:( darkTheme:!f,hidePanelTitles:! f,useMargins:!t),panels:!(), query:(language:lucene,query:\' \'),timeRestore:!f,title:\'New% 20Dashboard\',viewMode:edit)';
            return url;
        };

        return service;
    }
})();
