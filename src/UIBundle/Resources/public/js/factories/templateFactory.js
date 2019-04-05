(function (angular) {
    angular
        .module('leadwireApp')
        .factory('TemplateFactory', function ($http, CONFIG) {
            return {
                /**
                 *
                 * @returns {Promise}
                 */
                findAll: function () {
                    return $http.get(CONFIG.BASE_URL + 'api/template/list');
                },
                /**
                 *
                 * @returns {Promise}
                 */
                get: function (id) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/template/' + id + '/get');
                },
                /**
                 *
                 * @returns {Promise}
                 */
                new: function (newTemplate) {
                    return $http.post(CONFIG.BASE_URL + 'api/template/new',
                        newTemplate);
                },
                /**
                 *
                 * @returns {Promise}
                 */
                update: function (updatedTemplate) {
                    return $http.put(
                        CONFIG.BASE_URL + 'api/template/' + updatedTemplate.id +
                        '/update', updatedTemplate);
                },
                /**
                 *
                 * @returns {Promise}
                 */
                delete: function (id) {
                    return $http.delete(
                        CONFIG.BASE_URL + 'api/template/' + id + '/delete');
                },
                getTypes: function () {
                    return $http.get(CONFIG.BASE_URL + 'api/template/get-types');
                }
            };
        });
})(window.angular);
