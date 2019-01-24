(function (angular) {
    angular.module('leadwireApp').
        factory('ApplicationFactory', function ($http, CONFIG) {
            return {

                /**
                 *
                 * @returns {Promise}
                 */
                findMyApplications: function () {
                    return $http.get(CONFIG.BASE_URL + 'api/app/list');
                },
                /**
                 *
                 * @returns {Promise}
                 */
                findAll: function () {
                    return $http.get(CONFIG.BASE_URL + 'api/app/all');
                },
                /**
                 *
                 * @returns {Promise}
                 */
                findMyDashboard: function (id) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/app/' + id + '/dashboards',
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                findInvitedApps: function () {
                    return $http.get(CONFIG.BASE_URL + 'api/app/invited/list');
                },
                /**
                 *
                 * @returns {Promise}
                 */
                save: function (body) {
                    return $http.post(CONFIG.BASE_URL + 'api/app/new', body);
                },
                /**
                 *
                 * @returns {Promise}
                 */
                get: function (id) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/app/' + id + '/get',
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                update: function (id, body) {
                    return $http.put(
                        CONFIG.BASE_URL + 'api/app/' + id + '/update',
                        body,
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                remove: function (id) {
                    return $http.delete(
                        CONFIG.BASE_URL + 'api/app/' + id + '/delete',
                    );
                },
                /**
                 /**
                 *
                 * @returns {Promise}
                 */
                toggleStatus: function (id) {
                    return $http.put(
                        CONFIG.BASE_URL + 'api/app/' + id + '/lockToggle',
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                activate: function (id, code) {
                    return $http.post(
                        CONFIG.BASE_URL + 'api/app/' + id + '/activate',
                        { code: code },
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                stats: function (id) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/app/' + id + '/stats',
                    );
                },
            };
        });
})(window.angular);
