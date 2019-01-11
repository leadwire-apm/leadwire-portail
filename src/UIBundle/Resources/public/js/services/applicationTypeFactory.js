(function(angular) {
    angular
        .module('leadwireApp')
        .factory('ApplicationTypeFactory', [
            '$http',
            'CONFIG',
            ApplicationTypeFactoryFN
        ]);

    function ApplicationTypeFactoryFN($http, CONFIG) {
        return {
            findAll: function() {
                return $http.get(CONFIG.BASE_URL + 'api/applicationType/list');
            }
        };
    }
})(window.angular);
