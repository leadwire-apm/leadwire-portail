'use strict';

angular
    .module('leadwireApp')
    .config([
        '$stateProvider',
        '$urlRouterProvider',
        '$authProvider',
        'CONFIG',
        function ($stateProvider, $urlRouterProvider, $authProvider, CONFIG) {
            // For unmatched routes
            $urlRouterProvider.otherwise('/applications/list');

            /**
             *  Satellizer config
             */

            $authProvider.github({
                clientId: CONFIG.GITHUB_CLIENT_ID,
                url: CONFIG.BASE_URL + 'login/github'
            });

            var skipIfLoggedIn = [
                '$q',
                '$auth',
                function ($q, $auth) {
                    var deferred = $q.defer();
                    if ($auth.isAuthenticated()) {
                        deferred.reject();
                    } else {
                        deferred.resolve();
                    }
                    return deferred.promise;
                }
            ];

            var loginRequired = [
                '$q',
                '$location',
                '$auth',
                function ($q, $location, $auth) {
                    var deferred = $q.defer();
                    if ($auth.isAuthenticated()) {
                        deferred.resolve();
                    } else {
                        $location.path('/login');
                    }
                    return deferred.promise;
                }
            ];

            var adminRequired = [
                '$q',
                '$location',
                '$auth',
                '$localStorage',
                function ($q, $location, $auth, $localStorage) {
                    var deferred = $q.defer();
                    if ($auth.isAuthenticated()) {
                        if ($localStorage.user.roles
                            && $localStorage.user.roles.indexOf('ROLE_ADMIN') !== -1) {
                            deferred.resolve();
                        } else {
                            $location.path('/');
                        }
                    } else {
                        $location.path('/login');
                    }
                    return deferred.promise;
                }
            ];

            // Application routes
            $stateProvider
                .state('app', {
                    abstract: true,
                    templateUrl: 'common/layout.html'
                })
                .state('login', {
                    url: '/login',
                    templateUrl: 'extras-signin.html',
                    data: {
                        title: 'login'
                    },
                    controller: 'LoginCtrl',
                    controllerAs: 'ctrl'
                })
                .state('app.user', {
                    url: '/settings',
                    templateUrl: 'profile.html',
                    resolve: {
                        isModal: function () {
                            return false;
                        },
                        permissions: loginRequired,
                        menu: updateMenuItems('SETTINGS')
                    },
                    data: {
                        title: 'Settings'
                    },
                    controller: 'profileCtrl',
                    controllerAs: 'ctrl'
                })
                .state('app.applicationsAdd', {
                    url: '/applications/add',
                    templateUrl: 'application/add.html',
                    resolve: {
                        permissions: loginRequired,
                        menu: updateMenuItems('SETTINGS')
                    },
                    data: {
                        title: 'Add Application'
                    },
                    controller: 'addApplicationCtrl',
                    controllerAs: 'ctrl'
                })
                .state('app.toc', {
                    url: '/term-of-contract',
                    templateUrl: 'toc.html',
                    resolve: {
                        permissions: loginRequired
                    },
                    data: {
                        title: 'Term of Contract'
                    }
                })
                .state('app.tos', {
                    url: '/term-of-service',
                    templateUrl: 'tos.html',
                    resolve: {
                        permissions: loginRequired
                    },
                    data: {
                        title: 'Term of Service'
                    }
                })
                .state('app.applicationsList', {
                    url: '/applications/list',
                    templateUrl: 'application/list.html',
                    resolve: {
                        permissions: loginRequired,
                        deps: [
                            '$rootScope',
                            'MenuFactory',
                            'UserService',
                            function ($rootScope, MenuFactory, UserService) {
                                $rootScope.menus = MenuFactory.get('SETTINGS');
                                UserService.handleFirstLogin();
                                return Promise.resolve();
                            }
                        ]
                    },
                    data: {
                        title: 'Application list'
                    },
                    controller: 'applicationListCtrl',
                    controllerAs: 'ctrl'
                })
                .state('app.applicationDetail', {
                    url: '/applications/{id}/detail',
                    templateUrl: 'application/detail.html',
                    resolve: {
                        permissions: loginRequired,
                        menu: updateMenuItems('SETTINGS')
                    },
                    data: {
                        title: 'Application Detail'
                    },
                    controller: 'applicationDetailCtrl',
                    controllerAs: 'ctrl'
                })
                .state('app.applicationEdit', {
                    url: '/applications/{id}/edit',
                    templateUrl: 'application/edit.html',
                    resolve: {
                        permissions: loginRequired,
                        menu: updateMenuItems('SETTINGS')
                    },
                    data: {
                        title: 'Edit Application'
                    },
                    controller: 'applicationEditCtrl',
                    controllerAs: 'ctrl'
                })
                .state('app.billingList', {
                    url: '/billing/list',
                    templateUrl: 'billingList.html',
                    controller: 'billingListCtrl',
                    controllerAs: 'ctrl',
                    resolve: {
                        permissions: loginRequired,
                        deps: [
                            '$rootScope',
                            'MenuFactory',
                            function ($rootScope, MenuFactory) {
                                $rootScope.menus = MenuFactory.get('SETTINGS');
                                return Promise.resolve();
                            }
                        ]
                    }
                })
                .state('app.editPaymentMethod', {
                    url: '/payment/edit',
                    templateUrl: 'editPaymentMethod.html',
                    controller: 'editPaymentMethodCtrl',
                    controllerAs: 'ctrl',
                    resolve: {
                        permissions: loginRequired,
                        deps: [
                            '$rootScope',
                            'MenuFactory',
                            function ($rootScope, MenuFactory) {
                                $rootScope.menus = MenuFactory.get('SETTINGS');
                                return Promise.resolve();
                            }
                        ]
                    }
                })
                .state('app.updateSubscription', {
                    url: '/subscription/{action}',
                    templateUrl: 'updateSubscription.html',
                    controller: 'updateSubscriptionCtrl',
                    controllerAs: 'ctrl',
                    resolve: {
                        permissions: loginRequired,
                        deps: [
                            '$rootScope',
                            'MenuFactory',
                            function ($rootScope, MenuFactory) {
                                $rootScope.menus = MenuFactory.get('SETTINGS');
                                return Promise.resolve();
                            }
                        ]
                    }
                })
                .state('app.dashboard', {
                    abstract: true
                })
                .state('app.dashboard.home', {
                    url: '/dashboard/:id/:tenant',
                    templateUrl: 'dashboard.html',
                    resolve: {
                        permissions: loginRequired,
                        deps: [
                            'MenuFactory',
                            '$rootScope',
                            '$localStorage',
                            'UserService',
                            function (MenuFactory, $rootScope, $localStorage, UserService) {
                                $rootScope.menus = $localStorage.currentMenu;
                                UserService.handleFirstLogin();
                            }
                        ]
                    },
                    controller: 'dashboardCtrl',
                    controllerAs: 'ctrl'
                })
                .state('app.dashboard.customDashboard', {
                    url: '/dashboard/custom',
                    templateUrl: 'customDashboards.html',
                    controller: 'customDashboardsCtrl',
                    controllerAs: 'ctrl',
                    resolve: {
                        permissions: loginRequired
                    }
                })
                .state('app.dashboard.manageDashboard', {
                    url: '/dashboard/manage/{tenant}',
                    templateUrl: 'manageDashboards.html',
                    controller: 'manageDashboardsCtrl',
                    controllerAs: 'ctrl',
                    resolve: {
                        permissions: loginRequired
                    }
                })
                .state('app.management', {
                    abstract: true,
                    url: '/management'

                })
                .state('app.management.users', {
                    url: '/users/list',
                    templateUrl: 'management/users.html',
                    resolve: {
                        permissions: adminRequired,
                        menu: updateMenuItems('MANAGEMENT')
                    },
                    data: {
                        title: 'Management / Users'
                    },
                    controller: 'ManageUsersController',
                    controllerAs: 'ctrl'
                })
                .state('app.management.admins', {
                    url: '/admins/list',
                    templateUrl: 'management/admins.html',
                    resolve: {
                        permissions: adminRequired,
                        menu: updateMenuItems('MANAGEMENT')
                    },
                    data: {
                        title: 'Management / Admins'
                    },
                    controller: 'ManageAdminsController',
                    controllerAs: 'ctrl'
                })

                .state('app.management.plans', {
                    url: '/plans/list',
                    templateUrl: 'management/plans.html',
                    resolve: {
                        permissions: adminRequired,
                        menu: updateMenuItems('MANAGEMENT')
                    },
                    data: {
                        title: 'Management / Plans'
                    },
                    controller: 'PlanListController',
                    controllerAs: 'ctrl'
                })
                .state('app.infrastructureMonitoring', {
                    url: '/infrastructureMonitoring',
                    templateUrl: 'infrastructureMonitoring.html',
                    resolve: {
                        permissions: loginRequired
                    },
                    data: {
                        title: 'Infrastructure Monitoring'
                    },
                    controller: 'infrastructureMonitoringController',
                    controllerAs: 'ctrl'
                })
                .state('app.architectureDiscovery', {
                    url: '/architectureDiscovery',
                    templateUrl: 'architectureDiscovery.html',
                    resolve: {
                        permissions: loginRequired
                    },
                    data: {
                        title: 'Architecture Discovery'
                    },
                    controller: 'architectureDiscoveryController',
                    controllerAs: 'ctrl'
                })

                // Data Browser
                .state('app.dataBrowser', {
                    url: '/dataBrowser',
                    templateUrl: 'dataBrowser.html',
                    resolve: {
                        permissions: loginRequired
                    },
                    data: {
                        title: 'Data Browser'
                    },
                    controller: 'dataBrowserController',
                    controllerAs: 'ctrl'
                })

                // custom Reports
                .state('app.customReports', {
                    url: '/customReports',
                    templateUrl: 'customReports.html',
                    resolve: {
                        permissions: loginRequired
                    },
                    data: {
                        title: 'Custom Reports'
                    },
                    controller: 'customReportsController',
                    controllerAs: 'ctrl'
                })

                // Synthetic Monitoring

                .state('app.syntheticMonitoring', {
                    url: '/syntheticMonitoring',
                    templateUrl: 'syntheticMonitoring.html',
                    resolve: {
                        permissions: loginRequired
                    },
                    data: {
                        title: 'Synthetic Monitoring'
                    },
                    controller: 'syntheticMonitoringController',
                    controllerAs: 'ctrl'
                })

                // Alerts
                .state('app.alerts', {
                    url: '/alerts',
                    templateUrl: 'alerts.html',
                    resolve: {
                        permissions: loginRequired
                    },
                    data: {
                        title: 'Alerts'
                    },
                    controller: 'alertsController',
                    controllerAs: 'ctrl'
                })

                // Business Transactions
                .state('app.businessTransactions', {
                    url: '/businessTransactions',
                    templateUrl: 'businessTransactions.html',
                    resolve: {
                        permissions: loginRequired
                    },
                    data: {
                        title: 'Business Transactions'
                    },
                    controller: 'businessTransactionsController',
                    controllerAs: 'ctrl'
                })

                // real User Monitoring
                .state('app.realUserMonitoring', {
                    url: '/realUserMonitoring',
                    templateUrl: 'realUserMonitoring.html',
                    resolve: {
                        permissions: loginRequired
                    },
                    data: {
                        title: 'Real User Monitoring'
                    },
                    controller: 'realUserMonitoringController',
                    controllerAs: 'ctrl'
                })

                // Administration
                .state('app.administration', {
                    template: '<div ui-view></div>',
                    abstract: true,
                    url: '/administration'
                })
                .state('app.administration.visualisations', {
                    url: '/visualisations',
                    templateUrl: 'administration/visualisations.html',
                    resolve: {
                        permissions: loginRequired
                    },
                    data: {
                        title: 'Administration / Visualisations'
                    },
                    controller: 'administrationVisualisationsController',
                    controllerAs: 'ctrl'
                })
                .state('app.administration.reports', {
                    url: '/reports',
                    templateUrl: 'administration/reports.html',
                    resolve: {
                        permissions: loginRequired
                    },
                    data: {
                        title: 'Administration / Reports'
                    },
                    controller: 'administrationReportsController',
                    controllerAs: 'ctrl'
                })
                .state('logout', {
                    controller: 'logoutCtrl',
                    controllerAs: 'ctrl',
                    url: '/logout',
                    resolve: {},
                    data: {
                        title: 'login'
                    }
                })
                .state('static', {
                    abstract: true,
                    templateUrl: 'static/layout.html'
                })

                .state('static.privacyPolicy', {
                    url: '/page/privacy-policy',
                    templateUrl: 'static/privacyPolicy.html'
                })
                .state('static.termsOfService', {
                    url: '/page/terms-of-Service',
                    templateUrl: 'static/termsOfService.html'
                })
                .state('static.termsOfContacts', {
                    url: '/page/terms-of-Contract',
                    templateUrl: 'static/termsOfContract.html'
                })
                .state('static.help', {
                    url: '/page/help',
                    templateUrl: 'static/help.html'
                })
                .state('static.aboutUs', {
                    url: '/page/about-us',
                    templateUrl: 'static/aboutUs.html'
                });

            function updateMenuItems(key) {
                return [
                    'MenuFactory',
                    '$rootScope',
                    function (MenuFactory, $rootScope) {
                        $rootScope.menus = MenuFactory.get(key);
                        return Promise.resolve();
                    }
                ];
            }
        }
    ])
    .config([
        '$ocLazyLoadProvider',
        '$httpProvider',
        '$locationProvider',
        'MESSAGES_CONSTANTS',
        'toastrConfig',
        function (
            $ocLazyLoadProvider,
            $httpProvider,
            $locationProvider,
            MSG,
            toastrConfig
        ) {
            $ocLazyLoadProvider.config({
                debug: true,
                events: false
            });
            $httpProvider.interceptors.push('HttpInterceptor');
            angular.extend(toastrConfig, {
                allowHtml: false,
                closeButton: true,
                closeHtml: '<button>&times;</button>',
                progressBar: true
            });
        }
    ]);
