'use strict';

/*
 * offscreen - Off canvas sidebar directive
 */

function offscreen($rootScope, $timeout) {
    return {
        restrict: 'EA',
        replace: true,
        transclude: true,
        templateUrl: 'directives/toggle-offscreen.html',
        link: function(scope, element, attrs) {
            scope.offscreenDirection = attrs.move;
        },
        controller: function($scope, $element, $timeout) {
            var dir,
                offscreenDirectionClass;

            $scope.offscreen = function() {
                dir = $scope.offscreenDirection ?
                    $scope.offscreenDirection :
                    'ltr';

                if ($scope.app.layout.isChatOpen) {
                    $scope.app.layout.isChatOpen = !$scope.app.layout.isChatOpen;
                }

                if (dir === 'rtl' ||
                    angular.element('.app').hasClass('layout-right-sidebar')) {
                    offscreenDirectionClass = 'move-right';
                } else {
                    offscreenDirectionClass = 'move-left';
                }

                if ($scope.app.layout.isOffscreenOpen) {
                    angular.element('.app').removeClass('move-left move-right');
                    $timeout(function() {
                        angular.element('.app').removeClass('offscreen');
                    }, 300);
                    $scope.app.layout.isOffscreenOpen = false;
                } else {
                    angular.element('.app').
                        addClass('offscreen ' + offscreenDirectionClass);
                    $scope.app.layout.isOffscreenOpen = true;
                }
            };
        },
    };
}

angular.module('leadwireApp').directive('offscreen', offscreen);
