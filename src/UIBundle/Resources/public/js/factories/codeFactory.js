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
            };
        });
})(window.angular);
