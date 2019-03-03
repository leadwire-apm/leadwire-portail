(function (angular) {
    angular
        .module('leadwireApp')
        .factory('TmecFactory', function ($http, CONFIG) {
            return {
                /**
                 *
                 * @returns {Promise}
                 */
                new: function (tmec) {
                    return $http.post(
                        CONFIG.BASE_URL + 'api/tmec/new',
                        tmec);
                },
                /**
                 *
                 * @returns {Promise}
                 */
                list: function (applicationId) {
                    return $http.get(CONFIG.BASE_URL + 'api/tmec/list',
                    applicationId);
                },
            };
        });
})(window.angular);
