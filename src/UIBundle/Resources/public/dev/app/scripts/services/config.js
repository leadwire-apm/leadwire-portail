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
                return service.baseUrl + $localStorage.user.uuid + '/' + dashboardId + '&token=' +
                    $auth.getToken();
            } else {
                return service.baseUrl + $localStorage.user.uuid + '/' + dashboardId + '?token=' +
                    $auth.getToken();
            }
        };

        service.getDashboard = function(dashboardId, hasParameter) {
            return this.getUrl('app/kibana#/dashboard/' + dashboardId,
                hasParameter);
        };
        return service;
    }

})();