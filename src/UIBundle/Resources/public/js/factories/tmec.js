(function (angular) {
    angular
        .module('leadwireApp')
        .factory('TmecFactory', function ($http, CONFIG) {
            return {
                /**
                 *
                 * @returns {Promise}
                 */
                new: function (tmec) {
                    return $http.post(
                        CONFIG.BASE_URL + 'api/tmec/new',
                        tmec);
                },
                /**
                 *
                 * @returns {Promise}
                 */
                list: function (options) {
                    return $http.post(CONFIG.BASE_URL + 'api/tmec/list' + options );
                },
                /**
                 *
                 * @returns {Promise}
                 */
                find: function (id) {
                    return $http.get(CONFIG.BASE_URL + 'api/tmec/find/' + id);
                },
                 /**
                 *
                 * @returns {Promise}
                 */
                update: function (tmec) {
                    return $http.post(
                        CONFIG.BASE_URL + 'api/tmec/update', tmec);
                },
                /**
                 *
                 * @returns {Promise}
                 */
                delete: function (id) {
                    return $http.delete(
                        CONFIG.BASE_URL + 'api/tmec/delete/' + id);
                },              
                /**
                *
                * @returns {Promise}
                */
               listSteps: function (id) {
                   return $http.get(CONFIG.BASE_URL + 'api/step/list/' + id);
               },                 /**
               *
               * @returns {Promise}
               */
              updateStep: function (step) {
                  return $http.post(
                      CONFIG.BASE_URL + 'api/step/update', step);
              },
             /*
              * @returns {Promise}
              */
             all: function () {
                 return $http.get(
                     CONFIG.BASE_URL + 'api/tmec/all');
             },
            };
        });
})(window.angular);
