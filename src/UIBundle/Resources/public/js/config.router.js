'use strict';

angular
    .module('leadwireApp')
    .config([
        '$stateProvider',
        '$urlRouterProvider',
        '$authProvider',
        'CONFIG',
        function($stateProvider, $urlRouterProvider, $authProvider, CONFIG) {
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
                function($q, $auth) {
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
                function($q, $location, $auth) {
                    var deferred = $q.defer();
                    if ($auth.isAuthenticated()) {
                        deferred.resolve();
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
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load({
                                    name: 'sbAdminApp',
                                    files: [
                                        CONFIG.ASSETS_BASE_URL +
                                            'bundles/ui/js/controllers/login.js'
                                    ]
                                });
                            }
                        ]
                    },
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
                        isModal: function() {
                            return false;
                        },
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            'MenuFactory',
                            '$rootScope',
                            function($ocLazyLoad, MenuFactory, $rootScope) {
                                $rootScope.menus = MenuFactory.get('SETTINGS');
                                return $ocLazyLoad.load({
                                    name: 'sbAdminApp',
                                    files: [
                                        CONFIG.ASSETS_BASE_URL +
                                            'bundles/ui/js/controllers/profile.js'
                                    ]
                                });
                            }
                        ]
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
                        loginRequired: loginRequired,
                        deps: getNotyDeps([
                            CONFIG.ASSETS_BASE_URL +
                                'bundles/ui/js/controllers/addApplication.js'
                        ])
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
                        loginRequired: loginRequired
                    },
                    data: {
                        title: 'Term of Contract'
                    }
                })
                .state('app.tos', {
                    url: '/term-of-service',
                    templateUrl: 'tos.html',
                    resolve: {
                        loginRequired: loginRequired
                    },
                    data: {
                        title: 'Term of Service'
                    }
                })
                .state('app.applicationsList', {
                    url: '/applications/list',
                    templateUrl: 'application/list.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            '$rootScope',
                            'MenuFactory',
                            'UserService',
                            function(
                                $ocLazyLoad,
                                $rootScope,
                                MenuFactory,
                                UserService
                            ) {
                                $rootScope.menus = MenuFactory.get('SETTINGS');
                                UserService.handleFirstLogin();

                                return $ocLazyLoad.load({
                                    name: 'sbAdminApp',
                                    files: [
                                        CONFIG.ASSETS_BASE_URL +
                                            'bundles/ui/js/controllers/application.js'
                                    ]
                                });
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
                        loginRequired: loginRequired,
                        deps: getNotyDeps([
                            CONFIG.ASSETS_BASE_URL +
                                'bundles/ui/js/controllers/detailApplication.js'
                        ])
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
                        loginRequired: loginRequired,
                        deps: getNotyDeps([
                            CONFIG.ASSETS_BASE_URL +
                                'bundles/ui/js/controllers/editApplication.js'
                        ])
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
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            '$rootScope',
                            'MenuFactory',
                            function($ocLazyLoad, $rootScope, MenuFactory) {
                                $rootScope.menus = MenuFactory.get('SETTINGS');

                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'bundles/ui/js/controllers/billingList.js'
                                );
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
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            '$rootScope',
                            'MenuFactory',
                            function($ocLazyLoad, $rootScope, MenuFactory) {
                                $rootScope.menus = MenuFactory.get('SETTINGS');

                                return $ocLazyLoad
                                    .load({
                                        insertBefore: '#load_styles_before',
                                        files: [
                                                'css/chosen.min.css',
                                                'bundles/ui/js/lib/chosen.jquery.min.js',
                                                'bundles/ui/js/lib/jquery.card.js',
                                                'bundles/ui/js/lib/jquery.validate.min.js',
                                                'bundles/ui/js/lib/jquery.bootstrap.wizard.min.js'
                                        ]
                                    })
                                    .then(function() {
                                        return $ocLazyLoad.load(
                                            CONFIG.ASSETS_BASE_URL +
                                                'bundles/ui/js/controllers/editPaymentMethod.js'
                                        );
                                    });
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
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            '$rootScope',
                            'MenuFactory',
                            function($ocLazyLoad, $rootScope, MenuFactory) {
                                $rootScope.menus = MenuFactory.get('SETTINGS');
                                return $ocLazyLoad
                                    .load({
                                        insertBefore: '#load_styles_before',
                                        files: [
                                                'css/chosen.min.css',
                                                'bundles/ui/js/lib/chosen.jquery.min.js',
                                                'bundles/ui/js/lib/jquery.card.js',
                                                'bundles/ui/js/lib/jquery.validate.min.js',
                                                'bundles/ui/js/lib/jquery.bootstrap.wizard.min.js'
                                        ]
                                    })
                                    .then(function() {
                                        return $ocLazyLoad.load(
                                            CONFIG.ASSETS_BASE_URL +
                                                'bundles/ui/js/controllers/updateSubscription.js'
                                        );
                                    });
                            }
                        ]
                    }
                })
                .state('app.dashboard', {
                    abstract: true
                })
                .state('app.dashboard.home', {
                    url: '/dashboard/:id/:tenant',
                    templateUrl:'dashboard.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            'MenuFactory',
                            '$rootScope',
                            '$localStorage',
                            'UserService',
                            function(
                                $ocLazyLoad,
                                MenuFactory,
                                $rootScope,
                                $localStorage,
                                UserService
                            ) {
                                $rootScope.menus = $localStorage.currentMenu;
                                UserService.handleFirstLogin();
                                return $ocLazyLoad
                                    .load([
                                        {
                                            insertBefore: '#load_styles_before',
                                            files: [
                                                CONFIG.ASSETS_BASE_URL +
                                                    'styles/climacons-font.css',
                                                CONFIG.ASSETS_BASE_URL +
                                                    'vendor/rickshaw/rickshaw.min.css'
                                            ]
                                        },
                                        {
                                            serie: true,
                                            files: [
                                                CONFIG.ASSETS_BASE_URL +
                                                    'vendor/d3/d3.min.js',
                                                CONFIG.ASSETS_BASE_URL +
                                                    'vendor/rickshaw/rickshaw.min.js',
                                                CONFIG.ASSETS_BASE_URL +
                                                    'vendor/flot/jquery.flot.js',
                                                CONFIG.ASSETS_BASE_URL +
                                                    'vendor/flot/jquery.flot.resize.js',
                                                CONFIG.ASSETS_BASE_URL +
                                                    'vendor/flot/jquery.flot.pie.js',
                                                CONFIG.ASSETS_BASE_URL +
                                                    'vendor/flot/jquery.flot.categories.js'
                                            ]
                                        },
                                        {
                                            name: 'angular-flot',
                                            files: [
                                                CONFIG.ASSETS_BASE_URL +
                                                    'vendor/angular-flot/angular-flot.js'
                                            ]
                                        }
                                    ])
                                    .then(function() {
                                        return $ocLazyLoad.load(
                                            CONFIG.ASSETS_BASE_URL +
                                                'bundles/ui/js/controllers/dashboard.js'
                                        );
                                    });
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
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'bundles/ui/js/controllers/customDashboards.js'
                                );
                            }
                        ]
                    }
                })
                .state('app.dashboard.manageDashboard', {
                    url: '/dashboard/manage/{tenant}',
                    templateUrl: 'manageDashboards.html',
                    controller: 'manageDashboardsCtrl',
                    controllerAs: 'ctrl',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'bundles/ui/js/controllers/manageDashboards.js'
                                );
                            }
                        ]
                    }
                })
                .state('app.infrastructureMonitoring', {
                    url: '/infrastructureMonitoring',
                    templateUrl: 'infrastructureMonitoring.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'bundles/ui/js/controllers/infrastructureMonitoring.js'
                                );
                            }
                        ]
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
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'bundles/ui/js/controllers/architectureDiscovery.js'
                                );
                            }
                        ]
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
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'bundles/ui/js/controllers/dataBrowser.js'
                                );
                            }
                        ]
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
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'bundles/ui/js/controllers/customReports.js'
                                );
                            }
                        ]
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
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'bundles/ui/js/controllers/syntheticMonitoring.js'
                                );
                            }
                        ]
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
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'bundles/ui/js/controllers/alerts.js'
                                );
                            }
                        ]
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
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'bundles/ui/js/controllers/businessTransactions.js'
                                );
                            }
                        ]
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
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'bundles/ui/js/controllers/realUserMonitoring.js'
                                );
                            }
                        ]
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
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'bundles/ui/js/controllers/administration.visualisations.js'
                                );
                            }
                        ]
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
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'bundles/ui/js/controllers/administration.reports.js'
                                );
                            }
                        ]
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
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load({
                                    name: 'sbAdminApp',
                                    files: [
                                        CONFIG.ASSETS_BASE_URL +
                                            'bundles/ui/js/controllers/login.js'
                                    ]
                                });
                            }
                        ]
                    },
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

            function getNotyDeps(files) {
                return [
                    '$ocLazyLoad',
                    'MenuFactory',
                    '$rootScope',
                    function($ocLazyLoad, MenuFactory, $rootScope) {
                        $rootScope.menus = MenuFactory.get('SETTINGS');
                        return $ocLazyLoad
                            .load([
                                {
                                    insertBefore: '#load_styles_before',
                                    files: [
                                        CONFIG.ASSETS_BASE_URL +
                                            'css/chosen.min.css'
                                    ]
                                },
                                {
                                    serie: true,
                                    files: [
                                            'bundles/ui/js/lib/chosen.jquery.min.js',
                                        CONFIG.ASSETS_BASE_URL +
                                            'bundles/ui/js/lib/jquery.noty.packaged.min.js',
                                            'bundles/ui/js/extensions/noty-defaults.js'
                                    ]
                                }
                            ])
                            .then(function() {
                                return $ocLazyLoad.load({
                                    name: 'sbAdminApp',
                                    files: files
                                });
                            });
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
        function(
            $ocLazyLoadProvider,
            $httpProvider,
            $locationProvider,
            MSG,
            toastrConfig
        ) {
            $ocLazyLoadProvider.config({
                debug: false,
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
