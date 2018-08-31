(function(angular) {
    angular
        .module('leadwireApp')
        .run(function($rootScope, MenuFactory, $localStorage, CONFIG) {
            $rootScope.menus = $localStorage.currentMenu;
            $rootScope.applications = $localStorage.applications;
            $rootScope.dashboards = $localStorage.dashboards;
            $rootScope.ASSETS_BASE_URL = CONFIG.ASSETS_BASE_URL;
            $rootScope.DOWNLOAD_URL = CONFIG.DOWNLOAD_URL;
            $rootScope.UPLOAD_URL = CONFIG.UPLOAD_URL;
            $rootScope.$watch('applications', function(newVal) {
                $localStorage.applications = newVal;
            });
        })
        .controller('AppCtrl', [
            '$scope',
            '$state',
            '$rootScope',
            '$auth',
            '$location',
            '$http',
            '$localStorage',
            'ApplicationService',
            'DashboardService',
            'MESSAGES_CONSTANTS',
            'toastr',
            AppCtrlFN
        ]);

    function AppCtrlFN(
        $scope,
        $state,
        $rootScope,
        $auth,
        $location,
        $http,
        $localStorage,
        AppService,
        DashboardService,
        MESSAGES_CONSTANTS,
        toastr
    ) {
        onLoad();

        $scope.$on('user:updated', function(event, data) {
            $rootScope.user = data;
        });
        $scope.$on('update:image', function(event, data) {
            $scope.$broadcast('reload:src', data);
        });

        $scope.$on('set:apps', function(event, apps) {
            $scope.applications = $localStorage.applications = apps;
        });

        $scope.$on('set:contextApp', function(event, appId) {
            $scope.selectedAppId = $localStorage.selectedAppId = appId;
            $localStorage.selectedApp = $localStorage.applications.find(
                function(currApp) {
                    return currApp.id === appId;
                }
            );
            $scope.$emit('context:updated');
        });
        $scope.$on('set:customMenus', function(event, customMenus) {
            $localStorage.customMenus = customMenus;
            $scope.withCustom = $localStorage.customMenus.withCustom;
        });

        $scope.$on('activate:app', function(event, activatedApp) {
            $scope.applications = $localStorage.applications = (
                $localStorage.applications || ($localStorage.applications = [])
            ).map(function(currentApp) {
                return currentApp.id !== activatedApp.id
                    ? currentApp
                    : activatedApp;
            });
        });

        $scope.$on('new-application', function(event, newApp) {
            (
                $localStorage.applications || ($localStorage.applications = [])
            ).push(newApp);
            ($scope.applications || ($scope.applications = [])).push(newApp);
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
            true
        );

        $scope.getRandomArbitrary = function() {
            return Math.round(Math.random() * 100);
        };

        $scope.changeContextApp = function(app) {
            $scope.isChangingContext = true;
            DashboardService.fetchDashboardsByAppId(app.id)
                .then(function(response) {
                    console.log(response)
                    debugger;

                    $scope.isChangingContext = false;
                    $scope.selectedAppId = response.appId;
                    if (response.dashboards && response.dashboards.length) {
                        var firstDashboardLink =
                            '/dashboard/' + response.dashboards[0].id;
                        debugger
                        $location.path(firstDashboardLink);
                    }
                    $scope.$apply();
                })
                .catch(function() {
                    $scope.$apply(function() {
                        $scope.isChangingContext = false;
                    });
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                });
        };

        $scope.brandRedirectTo = function() {
            if ($localStorage.dashboards && $localStorage.dashboards.length) {
                return $state.go('app.dashboard.home', {
                    id: $localStorage.dashboards[0].id
                });
            } else {
                return $location.go('app.applicationList');
            }
        };

        $scope.logout = function() {
            delete $localStorage.user;
            delete $localStorage.currentMenu;
            delete $localStorage.applications;
            delete $localStorage.dashboards;
            delete $localStorage.selectedAppId;

            $auth.logout().then(function() {
                toastr.info(MESSAGES_CONSTANTS.LOGOUT_SUCCESS);
                $location.path('/login');
            });
        };

        function onLoad() {
            $scope.mobileView = 767;
            $scope.state = $state;
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
                    headerTheme: ''
                },
                isMessageOpen: false,
                isConfigOpen: false
            };

            $rootScope.user = $localStorage.user;
            $scope.applications = $localStorage.applications;
            $scope.selectedAppId = $localStorage.selectedAppId;
            $scope.withCustom = (
                $localStorage.customMenus || ($localStorage.customMenus = {})
            ).withCustom;
        }
    }
})(window.angular);
