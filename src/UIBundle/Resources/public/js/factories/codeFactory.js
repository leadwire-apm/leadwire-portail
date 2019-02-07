(function (angular) {
    angular
        .module('leadwireApp')
        .factory('CodeFactory', function ($http, CONFIG) {
            return {
                /**
                 *
                 * @returns {Promise}
                 */
                new: function (newCode) {
                    return $http.post(CONFIG.BASE_URL + 'api/activation-code/new',
                        newCode);
                },
                /**
                 *
                 * @returns {Promise}
                 */
                findAll: function () {
                    return $http.get(CONFIG.BASE_URL + 'api/code/list');
                },
            };
        });
})(window.angular);
