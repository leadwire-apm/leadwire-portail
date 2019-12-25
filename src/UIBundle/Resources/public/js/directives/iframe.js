'use strict';

/*
 * vector - jvectormap directive
 */
function iframeSetDimensionsOnload(iFrameService) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            element.on('load', function() {
                iFrameService.setDimensions(element);
            })
        }
    };
}

angular.module('leadwireApp').directive('iframeSetDimensionsOnload', iframeSetDimensionsOnload);
