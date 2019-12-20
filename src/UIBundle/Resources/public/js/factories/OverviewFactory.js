(function (angular) {
    angular
        .module('leadwireApp')
        .factory('OverviewFactory', function ($http, CONFIG) {
            return {
             /*
              * @returns {Promise}
              */
             getClusterInformations: function () {
                 return $http.get(
                     CONFIG.BASE_URL + 'api/overview/getClusterInformations');
             },
            };
        });
})(window.angular);
