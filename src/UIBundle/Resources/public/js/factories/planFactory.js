(function (angular) {
    angular
        .module('leadwireApp')
        .factory('PlanFactory', function ($http, CONFIG) {
            return {
                /**
                 *
                 * @returns {Promise}
                 */
                findAll: function () {
                    return $http.get(CONFIG.BASE_URL + 'api/plan/list');
                },
                /**
                 *
                 * @returns {Promise}
                 */
                new: function (newPlan) {
                    return $http.post(CONFIG.BASE_URL + 'api/plan/new', newPlan);
                },
                /**
                 *
                 * @returns {Promise}
                 */
                update: function (updatedPlan) {
                    return $http.put(CONFIG.BASE_URL + 'api/plan/' + updatedPlan.id + '/update', updatedPlan);
                },
                /**
                 *
                 * @returns {Promise}
                 */
                delete: function (id) {
                    return $http.delete(CONFIG.BASE_URL + 'api/plan/' + id + '/delete',);
                },
            };
        });
})(window.angular);
