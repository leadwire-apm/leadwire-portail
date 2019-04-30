(function (angular) {
    angular.module('leadwireApp')
        .factory('MonitoringSetFactory', [
            '$http',
            'CONFIG',
            MonitoringSetFactoryFN,
        ]);

    function MonitoringSetFactoryFN ($http, CONFIG) {
        return {
            /**
             *
             * @param monitoringSet
             * @returns {Promise}
             */
            new: function (monitoringSet) {
                return $http.post(
                    CONFIG.BASE_URL + 'api/monitoringSet/new', monitoringSet);
            },
            /**
             *
             * @returns {Promise}
             */
            findAll: function () {
                return $http.get(CONFIG.BASE_URL + 'api/monitoringSet/list');
            },
            /**
             *
             * @returns {Promise}
             */
            findAllValid: function () {
                return $http.get(CONFIG.BASE_URL + 'api/monitoringSet/list/true');
            },
            /**
             *
             * @returns {Promise}
             */
            get: function (id) {
                return $http.get(
                    CONFIG.BASE_URL + 'api/monitoringSet/' + id + '/get');
            },
            /**
             *
             * @param monitoringSet
             * @returns {Promise}
             */
            update: function (monitoringSet) {
                return $http.put(
                    CONFIG.BASE_URL + 'api/monitoringSet/' + monitoringSet.id +
                    '/update', monitoringSet);
            },
            /**
             *
             * @param monitoringSetId
             * @returns {Promise}
             */
            remove: function (monitoringSetId) {
                return $http.delete(
                    CONFIG.BASE_URL + 'api/monitoringSet/' + monitoringSetId +
                    '/delete',
                );
            },
        };
    }
})(window.angular);
