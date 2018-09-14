(function(angular) {
    /**
     * parse markdown to HTML
     */
    angular
        .module('leadwireApp')
        .provider('markdownConverter', function() {
            var opts = {};
            return {
                config: function(newOpts) {
                    opts = newOpts;
                },
                $get: function() {
                    return new window.showdown.Converter(opts);
                }
            };
        })
        .directive('markdown', [
            '$sanitize',
            'markdownConverter',
            function($sanitize, markdownConverter) {
                return {
                    restrict: 'AE',
                    link: function(scope, element, attrs) {
                        if (attrs.markdown) {
                            scope.$watch(attrs.markdown, function(newVal) {
                                var html = newVal
                                    ? $sanitize(
                                          markdownConverter.makeHtml(newVal)
                                      )
                                    : '';
                                element.html(html);
                            });
                        } else {
                            var html = $sanitize(
                                markdownConverter.makeHtml(element.text())
                            );
                            element.html(html);
                        }
                    }
                };
            }
        ]);
})(window.angular);