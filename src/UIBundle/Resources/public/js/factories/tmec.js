(function (angular) {
    angular
        .module('leadwireApp')
        .factory('TmecFactory', function ($http, CONFIG) {
            return {
                /**
                 *
                 * @returns {Promise}
                 */
                new: function (newCode) {
                    return $http.post(
                        CONFIG.BASE_URL + 'api/tmec/new',
                        newCode);
                },
                /**
                 *
                 * @returns {Promise}
                 */
                getAllById: function () {
                    return $http.get(CONFIG.BASE_URL + 'api/tmec/list');
                },
            };
        });
})(window.angular);
