(function (angular) {
    angular
        .module('leadwireApp')
        .controller('AppCtrl', [
            '$scope',
            '$state',
            '$rootScope',
            '$auth',
            '$location',
            '$sessionStorage',
            'ApplicationFactory',
            'UserService',
            'DashboardService',
            'EnvironmentService',
            'MESSAGES_CONSTANTS',
            'toastr',
            'Paginator',
            'CONFIG',
            AppCtrlFN,
        ]);

    function AppCtrlFN(
        $scope,
        $state,
        $rootScope,
        $auth,
        $location,
        $sessionStorage,
        ApplicationFactory,
        UserService,
        DashboardService,
        EnvironmentService,
        MESSAGES_CONSTANTS,
        toastr,
        Paginator,
        CONFIG,
    ) {

        onLoad();

        $scope.environments = [];
        $scope.title = "Admin settings";

        $scope.getIcon = function (menuName) {
            var icon = "";
            switch (menuName) {
                case "APM":
                    icon = "fa fa-eye";
                    break;
                case "Logs":
                    icon = "fa fa-list-alt";
                    break;
                case "Metrics":
                    icon = "fa fa-tachometer-alt";
                    break;
                case "Network":
                    icon = "fa fa-network-wired";
                    break;
                case "Uptime":
                    icon = "fa fa-history";
                    break;
                default:
                    break;
            }

            return icon;
        }

        $scope.getEnvironments = function () {
            $scope.getDefaultEnv();
            EnvironmentService.list()
                .then(function (environments) {
                    $scope.environments = environments;
                })
                .catch(function (error) {
                });
        }


        $scope.setSelectedEnv = function(environment){
            $scope.selectedEnvId = $sessionStorage.selectedEnvId = environment.id;
            $scope.selectedEnv = $sessionStorage.selectedEnv = environment;
            $rootScope.$broadcast('environment:updated');
            $scope.app.layout.isChatOpen = false;
            $location.path('/applicationsSverview');
        }

        $scope.LEADWIRE_COMPAGNE_ENABLED = CONFIG.LEADWIRE_COMPAGNE_ENABLED;
        $scope.LEADWIRE_LOGIN_METHOD = CONFIG.LEADWIRE_LOGIN_METHOD;
        $scope.LEADWIRE_LOGOUT_URL = CONFIG.LEADWIRE_LOGOUT_URL;

        $scope.$on('user:updated', function (event, data) {
            $rootScope.user = data;
        });
        $scope.$on('update:image', function (event, data) {
            $scope.$broadcast('reload:src', data);
        });
        $scope.$on('new:app', function (event, data) {
            UserService.get($sessionStorage.user.id)
                .then(function (user) {
                    $rootScope.user = $sessionStorage.user = user;
                    $scope.$apply();
                })
                .catch(function () {
                    $scope.$apply(function () {
                        $scope.isChangingContext = false;
                    });
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                })
                ;
        });

        $scope.$on('set:apps', function (event, apps) {
            $scope.applications = $sessionStorage.applications = apps;
            $scope.paginator = Paginator.create({
                start: 0,
                items: $scope.applications,
            });
        });

        $scope.$on('set:contextApp', function (event, appId) {
            $scope.selectedAppId = $sessionStorage.selectedAppId = appId;
            $sessionStorage.selectedApp = $sessionStorage.applications.find(
                function (currApp) {
                    return currApp.id === appId;
                },
            );
            $scope.$emit('context:updated');
        });

        $scope.$on('set:customMenus', function (event, customMenus) {
            $sessionStorage.customMenus = customMenus;
            $scope.withCustom = $sessionStorage.customMenus.withCustom;
        });

        $scope.$on('activate:app', function (event, activatedApp) {
            $scope.applications = $sessionStorage.applications = (
                $sessionStorage.applications || ($sessionStorage.applications = [])
            ).map(function (currentApp) {
                return currentApp.id !== activatedApp.id
                    ? currentApp
                    : activatedApp;
            });
        });

        if (angular.isDefined($sessionStorage.layout)) {
            $scope.app.layout = $sessionStorage.layout;
        } else {
            $sessionStorage.layout = $scope.app.layout;
        }

        $scope.$watch(
            'app.layout',
            function () {
                $sessionStorage.layout = $scope.app.layout;
            },
            true,
        );

        $scope.getRandomArbitrary = function () {
            return Math.round(Math.random() * 100);
        };

        $scope.changeContextApp = function (app) {
            $scope.isChangingContext = true;
            DashboardService.fetchDashboardsByAppId(app.id)
                .then(function (response) {
                    $scope.isChangingContext = false;
                    $scope.selectedAppId = response.appId;
                    $scope.$emit('update:title', "Dashboards " + app.name);
                    if (response.dashboards && response.dashboards.length) {
                        $state.go('app.dashboard.home', {
                            id: response.dashboards[0].id,
                            tenant: response.dashboards[0].tenant
                        });
                    } else {
                        $location.path('/dashboard/custom/' + response.appId);
                    }
                    $scope.$apply();
                })
                .catch(function () {
                    $scope.$apply(function () {
                        $scope.isChangingContext = false;
                    });
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                });
        };

        $scope.getDefaultEnv = function () {
            $scope.isChangingContextEnv = true;
            if ($sessionStorage.selectedEnvId && $sessionStorage.selectedEnv) {
                $scope.selectedEnvId = $sessionStorage.selectedEnvId;
                $scope.selectedEnv = $sessionStorage.selectedEnv;
                return;
            }

            EnvironmentService.getDefault()
                .then(function (response) {
                    $scope.isChangingContext = false;
                    $scope.selectedEnvId = $sessionStorage.selectedEnvId = response.id;
                    $scope.selectedEnv = $sessionStorage.selectedEnv = response;
                })
                .catch(function () {
                    $scope.$apply(function () {
                        $scope.isChangingContext = false;
                    });
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                });
        };

        $rootScope.setDefaultEnv = $scope.getDefaultEnv;

        $scope.brandRedirectTo = function () {
            if ($sessionStorage.dashboards && $sessionStorage.dashboards.length) {
                $state.go('app.dashboard.home', {
                    id: $sessionStorage.dashboards[0].id,
                });
            } else {
                $state.go('app.applicationsList');
            }
        };

        $scope.showMenu = function (menu) {
            if (Array.isArray(menu)) {
                var show = false;
                menu.map(el => {
                    if (el.visible)
                        show = true;
                })
                return show;
            }
        }

        $scope.loadApplications = function () {
            ApplicationFactory.findMyApplications().then(function (response) {
                $sessionStorage.applications = response.data;
                $scope.$emit('set:apps', response.data);
            }).catch(function () {
            });
        };

        window.onunload = () => {
            alert('okkk')
        }

        $scope.logout = function () {
            window.sessionStorage.clear();
            $auth.logout()
                .then(function () {
                    toastr.info(MESSAGES_CONSTANTS.LOGOUT_SUCCESS);
                    if (CONFIG.LEADWIRE_LOGIN_METHOD === "proxy")
                        window.location.href = CONFIG.LEADWIRE_LOGOUT_URL;
                    else
                        $location.path('/login');
                });
        };

        function onLoad() {

            $scope.paginator = Paginator.create({
                start: 0,
                items: $scope.applications,
            });

            $scope.mobileView = 767;
            $scope.state = $state;
            $scope.app = {
                name: 'leadwire',
                author: 'Nyasha',
                version: CONFIG.APP_VERSION,
                year: new Date().getFullYear(),
                LEADWIRE_LOGIN_METHOD: CONFIG.LEADWIRE_LOGIN_METHOD,
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

            $scope.isAdmin = function () {
                return UserService.isAdmin($sessionStorage.user);
            };

            $rootScope.user = $sessionStorage.user;
            $scope.applications = $sessionStorage.applications;
            $scope.selectedAppId = $sessionStorage.selectedAppId;
            $scope.withCustom = (
                $sessionStorage.customMenus || ($sessionStorage.customMenus = {})
            ).withCustom;            
        }
    }
})(window.angular);
