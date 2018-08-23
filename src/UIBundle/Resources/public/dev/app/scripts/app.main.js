(function(angular) {

    angular.module('leadwireApp').
        run(function($rootScope, MenuFactory, $localStorage, CONFIG) {
            $rootScope.menus = $localStorage.currentMenu;
            $rootScope.applications = $localStorage.applications;
            $rootScope.dashboards = $localStorage.dashboards;
            $rootScope.ASSETS_BASE_URL = CONFIG.ASSETS_BASE_URL;
            $rootScope.DOWNLOAD_URL = CONFIG.DOWNLOAD_URL;
            $rootScope.UPLOAD_URL = CONFIG.UPLOAD_URL;
            $rootScope.$watch('applications', function(newVal) {
                $localStorage.applications = newVal;
            });

            // $rootScope.$on('$stateChangeStart', function(evt, to, params) {
            //     if (to.redirectTo) {
            //         evt.preventDefault();
            //         console.log(to)
            //         $state.p(to.redirectTo, params, { location: 'replace' });
            //     }
            // });
        }).
        controller('AppCtrl', [
            '$scope',
            '$rootScope',
            '$auth',
            '$location',
            '$http',
            '$localStorage',
            'ApplicationService',
            'DashboardService',
            'MESSAGES_CONSTANTS',
            'toastr',
            function AppCtrl(
                $scope,
                $rootScope,
                $auth,
                $location,
                $http,
                $localStorage,
                AppService,
                DashboardService,
                MESSAGES_CONSTANTS,
                toastr,
            ) {
                $scope.mobileView = 767;

                $scope.app = {
                    name: 'leadwire',
                    author: 'Nyasha',
                    version: '1.0.0',
                    year: new Date().getFullYear(),
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
                $scope.$on('user:updated', function(event, data) {
                    $rootScope.user = data;
                });
                $scope.$on('update-image', function(event, data) {
                    $scope.$broadcast('reload-src', data);
                });
                $scope.$on('activate-app', function(event, app) {
                    $localStorage.applications = $localStorage.applications.filter(
                        function(currentApp) {
                            return currentApp.id !== app.id;
                        });
                    $localStorage.applications.push(app);
                    $scope.applications = $localStorage.applications;

                });

                $scope.$on('new-application', function(event, newApp) {
                    if (angular.isUndefined($localStorage.applications)) {
                        $localStorage.applications = [];
                        $scope.applications = [];
                    }
                    $localStorage.applications.push(newApp);
                    $scope.applications.push(newApp);
                });

                if (angular.isDefined($localStorage.layout)) {
                    $scope.app.layout = $localStorage.layout;
                } else {
                    $localStorage.layout = $scope.app.layout;
                }

                $scope.$watch(
                    'app.layout',
                    function() {
                        $localStorage.layout = $scope.app.layout;
                    },
                    true,
                );

                $scope.getRandomArbitrary = function() {
                    return Math.round(Math.random() * 100);
                };

                $scope.getApp = function(app) {
                    DashboardService.fetchDashboardsByAppId(app.id);
                };

                $scope.logout = function() {
                    delete $localStorage.user;
                    delete $localStorage.currentMenu;
                    delete $localStorage.applications;
                    $auth.logout().then(function() {
                        toastr.info(MESSAGES_CONSTANTS.LOGOUT_SUCCESS);
                        $location.path('/login');
                    });
                };
            },
        ]);

})(window.angular)