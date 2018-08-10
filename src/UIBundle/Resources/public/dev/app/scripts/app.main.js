'use strict';

angular.module('leadwireApp').run(function($rootScope, MenuFactory) {
    $rootScope.menus = MenuFactory.get('DASHBOARD');
}).controller('AppCtrl', [
    '$scope',
    '$rootScope',
    '$auth',
    '$location',
    '$http',
    '$localStorage',
    'ApplicationService',
    'MESSAGES_CONSTANTS',
    'toastr',
    function AppCtrl(
        $scope, $rootScope, $auth, $location, $http, $localStorage, AppService,
        MESSAGES_CONSTANTS,
        toastr) {

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

        $rootScope.user = $localStorage.user;
        $scope.applications = $localStorage.applications;
        //$localStorage.selectedApp =

        $scope.$on('user:updated', function(event, data) {
            $rootScope.user = data;
        });
        $scope.$on('update-image', function(event, data) {
            $scope.$broadcast('reload-src', data);
        });

        $scope.$on('new-application', function(event, newApp) {
            $scope.$broadcast('add-application', newApp);
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

        $scope.logout = function() {
            delete $localStorage.user;
            $auth.logout().then(function() {
                toastr.info(MESSAGES_CONSTANTS.LOGOUT_SUCCESS);
                $location.path('/login');
            });
        };

    },
]);
