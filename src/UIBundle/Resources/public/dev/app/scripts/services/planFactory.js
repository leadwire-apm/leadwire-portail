(function(angular) {
    angular
        .module('leadwireApp')
        .factory('PlanFactory', function($http, CONFIG) {
            return {
                findAll: function() {
                    return $http.get(CONFIG.BASE_URL + 'api/plan/list');
                }
            };
        });
})(window.angular);
