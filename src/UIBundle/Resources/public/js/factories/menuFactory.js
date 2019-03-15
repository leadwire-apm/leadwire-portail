(function (angular) {
    angular
        .module('leadwireApp')
        .factory('MenuFactory', [
            'Menus',
            '$state',
            'CONFIG',
            function (Menus, $state, CONFIG) {

                if(CONFIG.STRIPE_ENABLED === "true"){
                    Menus.SETTINGS.push({
                        route: 'app.billingList',
                        icon: 'fa fa-money',
                        label: 'Billing',
                    });

                    Menus.MANAGEMENT.push({
                        route: 'app.management.plans',
                        abstractRoute: 'app.management',
                        icon: 'fa fa-money',
                        label: 'Manage plans',
                    });
                }

                if(CONFIG.COMPAGNE_ENABLED){
                    Menus.MANAGEMENT.push( 
                        {
                            route: 'app.management.tmecs',
                            abstractRoute: 'app.management',
                            icon: 'fa fa-table',
                            label: 'Manage Compagnes',
                        },               
                        {
                            route: 'app.management.overview',
                            abstractRoute: 'app.management',
                            icon: 'fa fa-eye',
                            label: 'Compagnes overview',
                        }
                    );

                    Menus.SETTINGS.push({
                        route: 'app.tmecs',
                        icon: 'fa fa-table',
                        label: 'Compagnes',
                    },
                    {
                        route: 'app.overview',
                        icon: 'fa fa-eye',
                        label: 'Compagnes overview',
                    });
                }

                return {
                    get: function (menuKey) {
                        return Menus[menuKey]
                            ? Menus[menuKey].map(function (menu) {
                                return angular.extend({}, menu, {
                                    route: $state.href(menu.route),
                                });
                            })
                            : [];
                    },
                    set: function (
                        menus,
                        labelCallback,
                        routeCallback,
                        iconCallback,
                    ) {
                        try {
                            return menus.map(function (menu) {
                                return {
                                    label: labelCallback(menu),
                                    route: routeCallback(menu),
                                    icon: iconCallback(menu),
                                };
                            });
                        }
                        catch (e) {
                            return [];
                        }
                    },
                };
            },
        ])
        .constant('Menus', {
            DASHBOARD: [
                {
                    icon: 'fa fa-dashboard',
                    label: 'Dashboard',
                    route: 'app.realUserMonitoring',
                },
                {
                    icon: 'fa fa-eye',
                    label: 'Real User Monitoring',
                    route: 'app.syntheticMonitoring',
                },
                {
                    icon: 'fa fa-exchange',
                    label: 'Synthetic Monitoring',
                    route: 'app.infrastructureMonitoring',
                },
                {
                    icon: 'fa fa-search',
                    label: 'Infrastructure Monitoring',
                    route: 'app.customReports ',
                },
                {
                    icon: 'fa fa-file-text',
                    label: 'Custom Reports ',
                    route: 'app.realUserMonitoring',
                },
                {
                    icon: 'fa fa-table',
                    label: 'Data Browser',
                    route: 'app.realUserMonitoring',
                },
                {
                    icon: 'fa fa-briefcase',
                    label: 'Business Transactions',
                    route: 'app.realUserMonitoring',
                },
                {
                    icon: 'fa fa-sitemap',
                    label: 'Architecture Discovery',
                    route: 'app.realUserMonitoring',
                },
                {
                    icon: 'fa fa-exclamation-triangle',
                    label: 'Alerts',
                    route: 'app.realUserMonitoring',
                },
                {
                    icon: 'fa fa-book',
                    label: 'Documentation',
                    route: 'app.realUserMonitoring',
                },
                {
                    icon: 'fa fa-support',
                    label: 'Support',
                    route: 'app.realUserMonitoring',
                },
                {
                    icon: 'fa fa-gears',
                    label: 'Administration',
                    children: [
                        {
                            route: 'app.administration.visualisations',
                            label: 'Visualisations',
                        },
                        {
                            route: 'app.administration.reports',
                            label: 'Reports',
                        },
                    ],
                },
            ],
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
                    route: 'app.management.templates',
                    abstractRoute: 'app.management',
                    icon: 'fa fa-file-text',
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
