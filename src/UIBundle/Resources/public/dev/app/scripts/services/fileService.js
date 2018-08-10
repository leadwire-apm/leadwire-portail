angular.module('leadwireApp').factory('FileService', function($http, CONFIG) {
    return {
        upload: function(file, entity) {
            var fd = new FormData();
            fd.append('file', file);
            fd.append('entity', entity);
            return $http.post(CONFIG.BASE_URL + 'core/api/upload', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined},
            });
        },
    };
});