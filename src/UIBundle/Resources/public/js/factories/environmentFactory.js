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
                        CONFIG.BASE_URL + 'api/environment/' + id + '/get');
                },
                /**
                 *
                 * @returns {Promise}
                 */
                getMinimalist: function (id) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/environment/' + id + '/get/minimalist');
                },
                /**
                 *
                 * @returns {Promise}
                 */
                getAllMinimalist: function () {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/environment/list/minimalist');
                },
                /**
                 *
                 * @returns {Promise}
                 */
                update: function (body) {
                    return $http.put(
                        CONFIG.BASE_URL + 'api/environment/update',
                        body
                    );
                },
                 /**
                 *
                 * @returns {Promise}
                 */
                add: function (body) {
                    return $http.post(
                        CONFIG.BASE_URL + 'api/environment/new',
                        body
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                delete: function (id) {
                    return $http.delete(
                        CONFIG.BASE_URL + 'api/environment/' + id + '/delete');
                },
                /**
                 *
                 * @returns {Promise}
                 */
                setDefault: function ($id) {
                    return $http.put(
                        CONFIG.BASE_URL + 'api/environment/' + id + '/default',
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                getDefault: function () {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/environment/default',
                    );
                },
            };
        });
})(window.angular);
