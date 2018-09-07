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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/common/layout.html'
                })
                .state('login', {
                    url: '/login',
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/extras-signin.html',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load({
                                    name: 'sbAdminApp',
                                    files: [
                                        CONFIG.ASSETS_BASE_URL +
                                            'scripts/controllers/login.js'
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
                    templateUrl: CONFIG.ASSETS_BASE_URL + 'views/profile.html',
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
                                            'scripts/controllers/profile.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/application/add.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: getNotyDeps([
                            CONFIG.ASSETS_BASE_URL +
                                'scripts/controllers/addApplication.js'
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
                    templateUrl: CONFIG.ASSETS_BASE_URL + 'views/toc.html',
                    resolve: {
                        loginRequired: loginRequired
                    },
                    data: {
                        title: 'Term of Contract'
                    }
                })
                .state('app.tos', {
                    url: '/term-of-service',
                    templateUrl: CONFIG.ASSETS_BASE_URL + 'views/tos.html',
                    resolve: {
                        loginRequired: loginRequired
                    },
                    data: {
                        title: 'Term of Service'
                    }
                })
                .state('app.applicationsList', {
                    url: '/applications/list',
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/application/list.html',
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
                                            'scripts/controllers/application.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL +
                        'views/application/detail.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: getNotyDeps([
                            CONFIG.ASSETS_BASE_URL +
                                'scripts/controllers/detailApplication.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/application/edit.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: getNotyDeps([
                            CONFIG.ASSETS_BASE_URL +
                                'scripts/controllers/editApplication.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/billingList.html',
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
                                        'scripts/controllers/billingList.js'
                                );
                            }
                        ]
                    }
                })
                .state('app.updateSubscription', {
                    url: '/subscription/{action}',
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL +
                        'views/updateSubscription.html',
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
                                            $rootScope.ASSETS_BASE_URL +
                                                'vendor/chosen_v1.4.0/chosen.min.css',
                                            $rootScope.ASSETS_BASE_URL +
                                                'vendor/chosen_v1.4.0/chosen.jquery.min.js',
                                            $rootScope.ASSETS_BASE_URL +
                                                'vendor/card/lib/js/jquery.card.js',
                                            $rootScope.ASSETS_BASE_URL +
                                                'vendor/jquery-validation/dist/jquery.validate.min.js',
                                            $rootScope.ASSETS_BASE_URL +
                                                'vendor/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js'
                                        ]
                                    })
                                    .then(function() {
                                        return $ocLazyLoad.load(
                                            CONFIG.ASSETS_BASE_URL +
                                                'scripts/controllers/updateSubscription.js'
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
                    url: '/dashboard/{id}',
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/dashboard.html',
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
                                                'scripts/controllers/dashboard.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/customDashboards.html',
                    controller: 'customDashboardsCtrl',
                    controllerAs: 'ctrl',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'scripts/controllers/customDashboards.js'
                                );
                            }
                        ]
                    }
                })
                .state('app.dashboard.manageDashboard', {
                    url: '/dashboard/manage/{tenant}',
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/manageDashboards.html',
                    controller: 'manageDashboardsCtrl',
                    controllerAs: 'ctrl',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'scripts/controllers/manageDashboards.js'
                                );
                            }
                        ]
                    }
                })
                .state('app.infrastructureMonitoring', {
                    url: '/infrastructureMonitoring',
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL +
                        'views/infrastructureMonitoring.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'scripts/controllers/infrastructureMonitoring.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL +
                        'views/architectureDiscovery.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'scripts/controllers/architectureDiscovery.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/dataBrowser.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'scripts/controllers/dataBrowser.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/customReports.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'scripts/controllers/customReports.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL +
                        'views/syntheticMonitoring.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'scripts/controllers/syntheticMonitoring.js'
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
                    templateUrl: CONFIG.ASSETS_BASE_URL + 'views/alerts.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'scripts/controllers/alerts.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL +
                        'views/businessTransactions.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'scripts/controllers/businessTransactions.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL +
                        'views/realUserMonitoring.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'scripts/controllers/realUserMonitoring.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL +
                        'views/administration/visualisations.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'scripts/controllers/administration.visualisations.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL +
                        'views/administration/reports.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: [
                            '$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    CONFIG.ASSETS_BASE_URL +
                                        'scripts/controllers/administration.reports.js'
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
                                            'scripts/controllers/login.js'
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
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/static/layout.html'
                })

                .state('static.privacyPolicy', {
                    url: '/page/privacy-policy',
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL +
                        'views/static/privacyPolicy.html'
                })
                .state('static.termsOfService', {
                    url: '/page/terms-of-Service',
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL +
                        'views/static/termsOfService.html'
                })
                .state('static.termsOfContacts', {
                    url: '/page/terms-of-Contract',
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL +
                        'views/static/termsOfContract.html'
                })
                .state('static.help', {
                    url: '/page/help',
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/static/help.html'
                })
                .state('static.aboutUs', {
                    url: '/page/about-us',
                    templateUrl:
                        CONFIG.ASSETS_BASE_URL + 'views/static/aboutUs.html'
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
                                            'vendor/chosen_v1.4.0/chosen.min.css'
                                    ]
                                },
                                {
                                    serie: true,
                                    files: [
                                        CONFIG.ASSETS_BASE_URL +
                                            'vendor/chosen_v1.4.0/chosen.jquery.min.js',
                                        CONFIG.ASSETS_BASE_URL +
                                            'vendor/noty/js/noty/packaged/jquery.noty.packaged.min.js',
                                        CONFIG.ASSETS_BASE_URL +
                                            'scripts/extentions/noty-defaults.js'
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
