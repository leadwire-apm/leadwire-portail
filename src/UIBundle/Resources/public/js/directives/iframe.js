'use strict';

/*
 * vector - jvectormap directive
 */
function iframeSetDimensionsOnload() {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            element.on('load', function() {
                setElementDimension(element);
            })
        }
    };
}


function setElementDimension(element) {
    $('body').addClass('iframe');
    $('.main-content').css('margin', '-15px 0px 0px 0px');
    $('.main-content').css('padding', '80px 0px 0px 0px');
    var height = window.innerHeight - 120;
    var iFrameHeight = height + 'px'
    var iFrameWidth = '100%';
    element.css('width', iFrameWidth);
    element.css('height', iFrameHeight);
}

$(window).on('resize', function() {
    console.log('here');
    setElementDimension($('iframe'));
});

angular.module('leadwireApp').directive('iframeSetDimensionsOnload', iframeSetDimensionsOnload);
