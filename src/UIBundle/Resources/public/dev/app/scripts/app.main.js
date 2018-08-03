'use strict';

angular.module('leadwireApp').run(function($rootScope) {
    $rootScope.currentNav = 'application';
}).controller('AppCtrl', [
    '$scope', '$http', '$localStorage', 'CONFIG',
    function AppCtrl($scope, $http, $localStorage, CONFIG) {

        $scope.mobileView = 767;

        $scope.app = {
            name: 'leadwire',
            author: 'Nyasha',
            version: '1.0.0',
            year: (new Date()).getFullYear(),
            layout: {
                isSmallSidebar: false,
                isChatOpen: false,
                isFixedHeader: true,
                isFixedFooter: false,
                isBoxed: false,
                isStaticSidebar: false,
                isRightSidebar: false,
                isOffscreenOpen: false,
                isConversationOpen: false,
                isQuickLaunch: false,
                sidebarTheme: '',
                headerTheme: '',
            },
            isMessageOpen: false,
            isConfigOpen: false,
        };

        $scope.user = $localStorage.user;
        $scope.UPLOAD_URL = CONFIG.UPLOAD_URL;

        $scope.$on('user:updated', function(event, data) {
            $scope.user = data;
        });

        if (angular.isDefined($localStorage.layout)) {
            $scope.app.layout = $localStorage.layout;
        } else {
            $localStorage.layout = $scope.app.layout;
        }

        $scope.$watch('app.layout', function() {
            $localStorage.layout = $scope.app.layout;
        }, true);

        $scope.getRandomArbitrary = function() {
            return Math.round(Math.random() * 100);
        };
    },
]);
