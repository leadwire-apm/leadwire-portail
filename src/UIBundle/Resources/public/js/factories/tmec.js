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
                list: function (application) {
                    return $http.get(CONFIG.BASE_URL + 'api/tmec/list/' + application.application);
                },
                /**
                 *
                 * @returns {Promise}
                 */
                find: function (id) {
                    return $http.get(CONFIG.BASE_URL + 'api/tmec/find/' + id);
                },
            };
        });
})(window.angular);
