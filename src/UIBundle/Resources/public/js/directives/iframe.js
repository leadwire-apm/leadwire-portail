'use strict';

/*
 * vector - jvectormap directive
 */
function iframeSetDimensionsOnload() {
return {
    restrict: 'A',
    link: function(scope, element, attrs){

        element.on('load', function(){
            /* Set the dimensions here,
               I think that you were trying to do something like this: */
               //var iFrameHeight = element[0].contentWindow.document.body.scrollHeight + 'px';
               var iFrameHeight = '1400px'
               var iFrameWidth = '100%';
               element.css('width', iFrameWidth);
               element.css('height', iFrameHeight);
        })
    }};
}

angular.module('leadwireApp').directive('iframeSetDimensionsOnload', iframeSetDimensionsOnload);
