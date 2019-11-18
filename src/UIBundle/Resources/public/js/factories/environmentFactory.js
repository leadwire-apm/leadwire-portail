(function (angular) {
    angular.module('leadwireApp')
        .factory('EnvironementFactory', function ($http, CONFIG) {
            return {
                /**
                 *
                 * @returns {Promise}
                 */
                getAll: function () {
                    return $http.get(CONFIG.BASE_URL + 'api/environment/list');
                },
                /**
                 *
                 * @returns {Promise}
                 */
                get: function (id) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/environment/' + id + '/get',
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                update: function (id, body) {
                    return $http.put(
                        CONFIG.BASE_URL + 'api/environment/update',
                        body,
                    );
                },
                 /**
                 *
                 * @returns {Promise}
                 */
                add: function (body) {
                    return $http.put(
                        CONFIG.BASE_URL + 'api/environment/add',
                        body,
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                delete: function (id) {
                    return $http.delete(
                        CONFIG.BASE_URL + 'api/environment/' + id + '/delete',
                    );
                },
            };
        });
})(window.angular);
