(function(angular) {
    angular.module('leadwireApp').factory('FileService', [
        '$http',
        'CONFIG',
        function($http, CONFIG) {
            return {
                /**
                 * Helper to upload file to the server
                 *
                 * @param file
                 * @param entity
                 * @returns {Promise}
                 */
                upload: function(file, entity) {
                    var fd = new FormData();
                    fd.append('file', file);
                    fd.append('entity', entity);
                    return $http.post(CONFIG.BASE_URL + 'core/api/upload', fd, {
                        transformRequest: angular.identity,
                        headers: { 'Content-Type': undefined }
                    });
                }
            };
        }
    ]);
})(window.angular);
