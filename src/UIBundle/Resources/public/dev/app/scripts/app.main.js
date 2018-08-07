'use strict';

angular.module('leadwireApp').run(function($rootScope, MenuFactory) {
    $rootScope.menus = MenuFactory.get('DASHBOARD');
}).controller('AppCtrl', [
    '$scope',
    '$rootScope',
    '$http',
    '$localStorage',
    'ApplicationService',
    'CONFIG',
    function AppCtrl(
        $scope, $rootScope, $http, $localStorage, AppService, CONFIG) {

        $scope.mobileView = 767;
        $rootScope.currentNav = 'application';
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
        $scope.applications = $localStorage.applications;
        $localStorage.selectedApp =

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

        $scope.setDefaultApp = function(app) {
            AppService.setAppAsDefault(app);
        };

    },
]);
