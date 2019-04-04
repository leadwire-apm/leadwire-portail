(function (angular) {
    angular
        .module('leadwireApp')
        .factory('MenuFactory', [
            'Menus',
            '$state',
            'CONFIG',
            function (Menus, $state, CONFIG) {

                if(CONFIG.STRIPE_ENABLED === true){
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

                if(CONFIG.COMPAGNE_ENABLED === true){
                    Menus.MANAGEMENT.push(
                        {
                            route: 'app.management.tmecs',
                            abstractRoute: 'app.management',
                            icon: 'fa fa-table',
                            label: 'Manage Campaigns',
                        },
                    );

                    Menus.SETTINGS.push({
                        route: 'app.tmecs',
                        icon: 'fa fa-table',
                        label: 'Campaigns',
                    });
                }

                return {
                    get: function (menuKey) {
                        var menus = [];
                        if (menuKey in Menus) {
                            menus = Menus[menuKey].map(function (menu) {
                                return angular.extend({}, menu, {
                                    route: $state.href(menu.route),
                                });
                            });
                        }
                        return menus;
                    },
                    set: function (
                        menus,
                        labelCallback,
                        routeCallback,
                        iconCallback,
                    ) {
                        try {
                            newMenus = ['APM', 'Metricbeat System'];

                            apm = menus['APM'].map(function (menu) {
                                return {
                                    label: labelCallback(menu),
                                    route: routeCallback(menu),
                                    icon: iconCallback(menu),
                                };
                            });

                            infra = menus['Metricbeat System'].map(function (menu) {
                                return {
                                    label: labelCallback(menu),
                                    route: routeCallback(menu),
                                    icon: iconCallback(menu),
                                };
                            });

                            newMenus['APM'] = apm;
                            newMenus['Metricbeat System'] = infra;

                            return newMenus;
                        }
                        catch (e) {
                            console.log(e);
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
        })
        .constant('Menus', {
            DASHBOARD: [],
            SETTINGS: [
                {
                    route: 'app.user',
                    icon: 'fa fa-user',
                    label: 'Profile',
                },
                {
                    route: 'app.applicationsList',
                    icon: 'fa fa-desktop',
                    label: 'Applications',
                },
            ],
            MANAGEMENT: [
                {
                    route: 'app.management.users',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-user',
                    label: 'Manage Users',
                },
                {
                    route: 'app.management.applications',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-desktop',
                    label: 'Manage applications',
                },
                {
                    route: 'app.management.applicationTypes',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-desktop',
                    label: 'Manage application types',
                },
                {
                    route: 'app.management.monitoringSets',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-desktop',
                    label: 'Manage monitoring sets',
                },
                {
                    route: 'app.management.templates',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-file-alt',
                    label: 'Manage templates',
                },
                {
                    route: 'app.management.codes',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-qrcode',
                    label: 'Activation codes',
                },
            ],
        });
})(window.angular);
