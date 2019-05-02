(function (angular) {
    angular.module('leadwireApp')
        .factory('ApplicationTypeFactory', [
            '$http',
            'CONFIG',
            ApplicationTypeFactoryFN,
        ]);

    function ApplicationTypeFactoryFN ($http, CONFIG) {
        return {
            /**
             *
             * @param appType
             * @returns {Promise}
             */
            new: function (appType) {
                return $http.post(
                    CONFIG.BASE_URL + 'api/applicationType/new', appType);
            },
            /**
             *
             * @returns {Promise}
             */
            findAll: function () {
                return $http.get(CONFIG.BASE_URL + 'api/applicationType/list');
            },
            /**
             *
             * @returns {Promise}
             */
            get: function (id) {
                return $http.get(
                    CONFIG.BASE_URL + 'api/applicationType/' + id + '/get');
            },
            /**
             *
             * @param appType
             * @returns {Promise}
             */
            update: function (appType) {
                return $http.put(
                    CONFIG.BASE_URL + 'api/applicationType/' + appType.id +
                    '/update', appType);
            },
            /**
             *
             * @param appTypeId
             * @returns {Promise}
             */
            remove: function (appTypeId) {
                return $http.delete(
                    CONFIG.BASE_URL + 'api/applicationType/' + appTypeId +
                    '/delete',
                );
            }
        };
    }
})(window.angular);
