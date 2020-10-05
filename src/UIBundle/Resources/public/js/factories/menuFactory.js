(function (angular) {
    angular
        .module('leadwireApp')
        .factory('MenuFactory', [
            'Menus',
            '$state',
            'CONFIG',
            'UserService',
            '$sessionStorage',
            function (Menus, $state, CONFIG, UserService, $sessionStorage) {

                if (CONFIG.LEADWIRE_STRIPE_ENABLED === true) {
                    Menus.SETTINGS.push({
                        route: 'app.billingList',
                        icon: 'fa fa-money-bill-alt',
                        label: 'Billing',
                    });

                    Menus.MANAGEMENT.push({
                        route: 'app.management.plans',
                        abstractRoute: 'app.management',
                        icon: 'fa fa-money-bill-alt',
                        label: 'Manage plans',
                    });
                }

                if (CONFIG.LEADWIRE_LOGIN_METHOD === 'github') {
                    Menus.MANAGEMENT.push(
                        {
                            route: 'app.management.codes',
                            abstractRoute: 'app.management',
                            icon: 'fa fa-qrcode',
                            label: 'Activation codes',
                        }
                    )
                }

                function normalizeRouteParams(params) {
                    if (!_.has(params, 'ls')) return params;
                    var result = Object.keys(params.ls).reduce(function (acc, current) {
                        acc[current] = _.get($sessionStorage, params.ls[current]);
                        return acc;
                    }, params);
                    delete result.ls;

                    return result;
                }

                return {
                    update: function () {

                    },
                    get: function (menuKey) {
                        var menus = [];
                        if (menuKey in Menus) {
                            menus = Menus[menuKey].map(function (menu) {

                                return angular.extend({}, menu, {
                                    route: $state.href(menu.route, normalizeRouteParams(menu.params)),
                                    routeName: menu.route
                                });
                            });
                        }
                        if (menuKey === "CAMPAGNE") {

                            if (CONFIG.LEADWIRE_COMPAGNE_ENABLED === true) {
                                if (UserService.isAdmin($sessionStorage.user)) {
                                    menus.push(
                                        {
                                            route: $state.href('app.management.tmecs'),
                                            abstractRoute: 'app.management',
                                            icon: 'fa fa-table',
                                            label: 'Manage Campaigns',
                                        },
                                    );
                                } else {
                                    menus.push(
                                        {
                                            route: $state.href('app.tmecs'),
                                            icon: 'fa fa-table',
                                            label: 'Campaigns',
                                        }
                                    )
                                }
                                menus.push({
                                    url: CONFIG.LEADWIRE_JENKINS_URL,
                                    icon: 'fa fa-play-circle',
                                    label: 'Launch',
                                    external: true
                                })
                            }
                        }
                        return menus;
                    },
                    set: function (
                        menus,
                        labelCallback,
                        routeCallback,
                        iconCallback,
                        visibleCallback,
                        routeNameCallback,
                        routeOptsCallback,
                        routeIdCallback
                    ) {
                        try {
                            newMenus = {};

                            Object.keys(menus).forEach(function (theme) {
                                sub = menus[theme].map(function (menu) {
                                    return {
                                        label: labelCallback(menu),
                                        route: routeCallback(menu),
                                        icon: iconCallback(menu),
                                        visible: visibleCallback(menu),
                                        routeName: routeNameCallback(menu),
                                        routeOpts: routeOptsCallback(menu),
                                        routeId: routeIdCallback(menu)
                                    };
                                });

                                newMenus[theme] = sub;
                            })


                            // infra = menus['Metricbeat System'].map(function (menu) {
                            //     return {
                            //         label: labelCallback(menu),
                            //         route: routeCallback(menu),
                            //         icon: iconCallback(menu),
                            //     };
                            // });


                            // newMenus['Metricbeat System'] = infra;

                            return newMenus;
                        }
                        catch (e) {
                            return [];
                        }
                    },
                };
            },
        ])
        .constant('MenuEnum', {
            DASHBOARD: 'DASHBOARD',
            SETTINGS: 'SETTINGS',
            MANAGEMENT: 'MANAGEMENT',
            CAMPAGNE: 'CAMPAGNE',
        })
        .constant('Menus', {
            CAMPAGNE: [
                {
                    route: 'app.overview',
                    icon: 'fa fa-paper-plane',
                    label: 'Overview',
                }
            ],
            DASHBOARD: [],
            SETTINGS: [
                {
                    route: 'app.applicationsList',
                    icon: 'fa fa-window-restore',
                    label: 'Applications',
                },
                {
                    route: 'app.user',
                    icon: 'fa fa-user-tie',
                    label: 'Profile',
                },
            ],
            MANAGEMENT: [

                {
                    route: 'app.management.applications',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-window-restore',
                    label: 'Applications',
                },
                {
                    route: 'app.management.users',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-user-cog',
                    label: 'Users',
                },
                {
                    route: 'app.management.environmentList',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-sitemap',
                    label: 'Environments',
                },
                {
                    route: 'app.management.alerts',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-exclamation-triangle',
                    label: 'Alerts'
                },
                {
                    route: 'app.management.anomaly',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-heartbeat',
                    label: 'Anomaly Detectors ',
                },
                {
                    route: 'app.management.reports',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-th-list',
                    label: 'Reports',
                    params: {
                        ls: {
                            tenant: 'user.userIndex'
                        }
                    }
                },
                {
                    route: 'app.management.applicationTypes',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-tools',
                    label: 'Application types',
                },
                {
                    route: 'app.management.monitoringSets',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-desktop',
                    label: 'Monitoring sets',
                },
                {
                    route: 'app.management.templates',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-file-code',
                    label: 'Templates',
                },

                {
                    route: 'app.management.index',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-cogs',
                    label: 'Index state management'
                },
                {
                    route: 'app.management.security',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-shield-alt',
                    label: 'Security ',
                }
            ],
        });
})(window.angular);
