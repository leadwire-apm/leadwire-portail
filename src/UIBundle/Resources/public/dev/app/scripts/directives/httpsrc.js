angular.module('leadwireApp').directive('httpSrc', [
    '$http', '$rootScope', function($http, $rootScope) {
        var directive = {
            link: link,
            restrict: 'A',
        };

        return directive;

        function link(scope, element, attrs) {

            if (attrs.httpSrc.indexOf('http') !== -1) {
                attrs.$set('src', attrs.httpSrc);
            } else {
                loadImageFromServer(attrs.httpSrc);
            }

            //on change url
            scope.$on("reload-src", function (event, newImageName) {
                loadImageFromServer(newImageName);
            });



            function loadImageFromServer(src) {
                var url = $rootScope.DOWNLOAD_URL +
                    src.replace('.', '-');
                var requestConfig = {
                    method: 'Get',
                    url: url,
                    responseType: 'arraybuffer',
                    cache: 'true',
                };
                $http(requestConfig).then(function(response) {
                    var arr = new Uint8Array(response.data);

                    var raw = '';
                    var i, j, subArray, chunk = 5000;
                    for (i = 0, j = arr.length; i < j; i += chunk) {
                        subArray = arr.subarray(i, i + chunk);
                        raw += String.fromCharCode.apply(null, subArray);
                    }

                    var b64 = btoa(raw);

                    attrs.$set('src', 'data:image/jpeg;base64,' + b64);
                });
            }


        }

    },
]);