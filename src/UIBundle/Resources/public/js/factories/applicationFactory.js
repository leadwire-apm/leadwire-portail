(function (angular) {
    angular.module('leadwireApp')
        .factory('ApplicationFactory', function ($http, CONFIG, $rootScope) {
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
                findMyApplicationsById: function (id) {
                    return $http.get(CONFIG.BASE_URL + 'api/app/list/'+id);
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
                findAllMinimalist: function () {
                    return $http.get(CONFIG.BASE_URL + 'api/app/all/minimalist');
                },
                /**
                 *
                 * @returns {Promise}
                 */
                findMyDashboard: function (id, envName) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/app/' + id + '/dashboards/' + envName,
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                findMyReports: function (id, envName) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/app/' + id + '/reports/' + envName,
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
                get: function (id, scope = "Default") {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/app/' + id + '/get/' + scope,
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
                updateDashbaords: function (id, envId, body) {
                    return $http.put(
                        CONFIG.BASE_URL + `api/app/${id}/${envId}/update-dashboards`,
                        body,
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                delete: function (id) {
                    return $http.delete(
                        CONFIG.BASE_URL + 'api/app/' + id + '/delete',
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                purge: function (id) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/app/' + id + '/purge',
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                remove: function (id) {
                    return $http.delete(
                        CONFIG.BASE_URL + 'api/app/' + id + '/remove',
                    );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                toggleEnabled: function (id) {
                    return $http.put(
                        CONFIG.BASE_URL + 'api/app/' + id + '/activate-toggle',
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
                /**
                 *
                 * @returns {Promise}
                 */
                applyChanges: function (id) {
                    return $http.put(
                        CONFIG.BASE_URL + 'api/app/' + id + '/apply-change',
                    );
                },
                /**
                 * 
                 */
                getApplicationDocumentsCount: function (appName, envName) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/app/' + appName + '/' + envName + '/documents',
                    );
                },
                /**
                * 
                */
                getApplicationReports: function (appName, envName) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/app/' + appName + '/' + envName + '/reports',
                    );
                },
                /**
                * 
                */
                deleteApplicationReport: function (id, index) {
                    return $http.get(
                        CONFIG.BASE_URL + 'api/app/' + id + '/' + index + '/report',
                    );
                },
                /**
                *
                * @returns {Promise}
                */
               grantUser: function (appid, userid) {
                return $http.get(
                    CONFIG.BASE_URL + 'api/app/' + appid + '/' + userid + '/grantPermission'
                );
            },
            revokePermission: function (appid, userid) {
                return $http.get(
                    CONFIG.BASE_URL + 'api/app/' + appid + '/' + userid + '/revokePermission'
                );
            },
            };
        });
})(window.angular);
