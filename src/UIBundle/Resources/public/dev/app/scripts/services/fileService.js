angular.module('leadwireApp').factory('FileService', function($http) {
    return {
        upload: function(file, entity) {
            var fd = new FormData();
            fd.append('file', file);
            fd.append('entity', entity);
            return $http.post('http://localhost:9000/core/api/upload', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined},
            });
        },
    };
});