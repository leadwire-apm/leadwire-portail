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
                return service.baseUrl + $localStorage.user.uuid  + '&token=' +
                $auth.getToken() + dashboardId  ;
            } else {
                return service.baseUrl + $localStorage.user.uuid + '?token=' +
                $auth.getToken() +  dashboardId ;
            }
        };

        service.getDashboard = function(dashboardId, hasParameter) {
           var url = this.getUrl('#/dashboard/' + dashboardId,
                hasParameter);
           console.log(url);
           return url;
        };
        return service;
    }

})();