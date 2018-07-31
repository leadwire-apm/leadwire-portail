'use strict';

angular
    .module('leadwireApp')
    .config(['$stateProvider', '$urlRouterProvider', '$authProvider',
        function ($stateProvider, $urlRouterProvider, $authProvider) {
            var baseUrl = 'bundles/ui/app/';
            // For unmatched routes
            $urlRouterProvider.otherwise('/');

            /**
             *  Satellizer config
             */

            $authProvider.github({
                /*prod*/  //clientId: '5ae68ff984489a4ed647'
                /*test*/   clientId: 'a5b3aee9593a1aaa5046',
                /*local*/  // clientId: '094c2b7f0e14da4d0ca8',
                url: '/api/auth/github'

            });

            var skipIfLoggedIn = ['$q', '$auth', function($q, $auth) {
                var deferred = $q.defer();
                if ($auth.isAuthenticated()) {
                    deferred.reject();
                } else {
                    deferred.resolve();
                }
                return deferred.promise;
            }];

            var loginRequired = ['$q', '$location', '$auth', function($q, $location, $auth) {
                var deferred = $q.defer();
                if ($auth.isAuthenticated()) {
                    deferred.resolve();
                } else {
                    $location.path('/login');
                }
                return deferred.promise;
            }];


            // Application routes
            $stateProvider
                .state('app', {
                    abstract: true,
                    templateUrl: baseUrl + 'views/common/layout.html'
                })

                .state('login', {
                    url: '/login',
                    templateUrl: baseUrl + 'views/extras-signin.html',
                    resolve: {
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                name: 'sbAdminApp',
                                files: [baseUrl + 'scripts/controllers/login.js']
                            });
                        }]
                    },
                    data: {
                        title: 'login'
                    },
                    controller: 'LoginCtrl',
                    controllerAs: 'ctrl'
                })
                .state('app.user', {
                    url: '/settings',
                    templateUrl: baseUrl + 'views/profile.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                name: 'sbAdminApp',
                                files: [baseUrl + 'scripts/controllers/settings.js']
                            });
                        }]
                    },
                    data: {
                        title: 'Settings'
                    },
                    controller: 'settingsCtrl',
                    controllerAs: 'ctrl'
                })
                .state('app.applicationsAdd', {
                    url: '/applications/add',
                    templateUrl: baseUrl + 'views/application/form.html',
                    resolve: {
                        loginRequired: loginRequired,
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                name: 'sbAdminApp',
                                files: [baseUrl + 'scripts/controllers/application.js']
                            });
                        }]
                    },
                    data: {
                        title: 'Add Application'
                    },
                    controller: 'formApplicationCtrl',
                    controllerAs: 'ctrl'
                })
                .state('app.dashboard', {
                    url: '/',
                    templateUrl: baseUrl + 'views/dashboard.html',
                    resolve: {
                        loginRequired:loginRequired,
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                {
                                    insertBefore: '#load_styles_before',
                                    files: [
                                        baseUrl +'styles/climacons-font.css',
                                        baseUrl + 'vendor/rickshaw/rickshaw.min.css'
                                    ]
                                },
                                {
                                    serie: true,
                                    files: [
                                        baseUrl + 'vendor/d3/d3.min.js',
                                        baseUrl + 'vendor/rickshaw/rickshaw.min.js',
                                        baseUrl + 'vendor/flot/jquery.flot.js',
                                        baseUrl + 'vendor/flot/jquery.flot.resize.js',
                                        baseUrl + 'vendor/flot/jquery.flot.pie.js',
                                        baseUrl + 'vendor/flot/jquery.flot.categories.js'
                                    ]
                                },
                                {
                                    name: 'angular-flot',
                                    files: [
                                        baseUrl + 'vendor/angular-flot/angular-flot.js'
                                    ]
                                }]).then(function () {
                                return $ocLazyLoad.load(baseUrl + 'scripts/controllers/dashboard.js');
                            });
                        }]
                    },
                    data: {
                        title: 'Dashboard'
                    },
                    controller: 'dashboardCtrl',
                    controllerAs: 'ctrl'
                })

                .state('app.infrastructureMonitoring', {
                    url: '/infrastructureMonitoring',
                    templateUrl: baseUrl + 'views/infrastructureMonitoring.html',
                    resolve: {
                        loginRequired:loginRequired,
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load(baseUrl + 'scripts/controllers/infrastructureMonitoring.js');
                        }]
                    },
                    data: {
                        title: 'Infrastructure Monitoring'
                    },
                    controller: 'infrastructureMonitoringController',
                    controllerAs: 'ctrl'
                })

                .state('app.architectureDiscovery', {
                    url: '/architectureDiscovery',
                    templateUrl: baseUrl + 'views/architectureDiscovery.html',
                    resolve: {
                        loginRequired:loginRequired,
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load(baseUrl + 'scripts/controllers/architectureDiscovery.js');
                        }]
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
                    templateUrl: baseUrl + 'views/dataBrowser.html',
                    resolve: {
                        loginRequired:loginRequired,
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load(baseUrl + 'scripts/controllers/dataBrowser.js');
                        }]
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
                    templateUrl: baseUrl + 'views/customReports.html',
                    resolve: {
                        loginRequired:loginRequired,
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load(baseUrl + 'scripts/controllers/customReports.js');
                        }]
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
                    templateUrl: baseUrl + 'views/syntheticMonitoring.html',
                    resolve: {
                        loginRequired:loginRequired,
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load(baseUrl + 'scripts/controllers/syntheticMonitoring.js');
                        }]
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
                    templateUrl: baseUrl + 'views/alerts.html',
                    resolve: {
                        loginRequired:loginRequired,
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load(baseUrl + 'scripts/controllers/alerts.js');
                        }]
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
                    templateUrl: baseUrl + 'views/businessTransactions.html',
                    resolve: {
                        loginRequired:loginRequired,
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load(baseUrl + 'scripts/controllers/businessTransactions.js');
                        }]
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
                    templateUrl: baseUrl + 'views/realUserMonitoring.html',
                    resolve: {
                        loginRequired:loginRequired,
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load(baseUrl + 'scripts/controllers/realUserMonitoring.js');
                        }]
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
                    templateUrl: baseUrl + 'views/administration/visualisations.html',
                    resolve: {
                        loginRequired:loginRequired,
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load(baseUrl + 'scripts/controllers/administration.visualisations.js');
                        }]
                    },
                    data: {
                        title: 'Administration / Visualisations'
                    },
                    controller: 'administrationVisualisationsController',
                    controllerAs: 'ctrl'
                })

                .state('app.administration.reports', {
                    url: '/reports',
                    templateUrl: baseUrl + 'views/administration/reports.html',
                    resolve: {
                        loginRequired:loginRequired,
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load(baseUrl + 'scripts/controllers/administration.reports.js');
                        }]
                    },
                    data: {
                        title: 'Administration / Reports'
                    },
                    controller: 'administrationReportsController',
                    controllerAs: 'ctrl'
                })

        }


    ])
    .config(['$ocLazyLoadProvider' , '$httpProvider' , '$locationProvider', function ($ocLazyLoadProvider, $httpProvider, $locationProvider) {
        $ocLazyLoadProvider.config({
            debug: false,
            events: false
        });

        $httpProvider.interceptors.push(function($q, $location) {
            return {
                request: function (config) {
                    config.headers = config.headers || {};
                    return config;
                },
                responseError: function(response) {
                    console.log("status: ", response.status);

                    if (response.status == 401) {
                        console.log("response", response);
                        $location.path('/login');
                    }
                    return response || $q.when(response);
                }
            };
        });

    }]);
