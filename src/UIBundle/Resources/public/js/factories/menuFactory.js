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
        .constant('MenuEnum', {
            DASHBOARD: 'DASHBOARD',
            SETTINGS: 'SETTINGS',
            MANAGEMENT: 'MANAGEMENT',
        })
        .constant('Menus', {
            DASHBOARD: [
                // {
                //     icon: 'fa fa-dashboard',
                //     label: 'Dashboard',
                //     route: 'app.dashboard.home',
                // },
                // {
                //     icon: 'fa fa-eye',
                //     label: 'Real User Monitoring',
                //     route: 'app.realUserMonitoring',
                // },
                // {
                //     icon: 'fa fa-exchange',
                //     label: 'Synthetic Monitoring',
                //     route: 'app.syntheticMonitoring',
                // },
                // {
                //     icon: 'fa fa-search',
                //     label: 'Infrastructure Monitoring',
                //     route: 'app.infrastructureMonitoring',
                // },
                // {
                //     icon: 'fa fa-file-text',
                //     label: 'Custom Reports',
                //     route: 'app.customReports',
                // },
                // {
                //     icon: 'fa fa-table',
                //     label: 'Data Browser',
                //     route: 'app.dataBrowser',
                // },
                // {
                //     icon: 'fa fa-briefcase',
                //     label: 'Business Transactions',
                //     route: 'app.businessTransactions',
                // },
                // {
                //     icon: 'fa fa-sitemap',
                //     label: 'Architecture Discovery',
                //     route: 'app.architectureDiscovery',
                // },
                // {
                //     icon: 'fa fa-exclamation-triangle',
                //     label: 'Alerts',
                //     route: 'app.alerts',
                // },
                // {
                //     icon: 'fa fa-book',
                //     label: 'Documentation',
                //     route: 'app.realUserMonitoring',
                // },
                // {
                //     icon: 'fa fa-support',
                //     label: 'Support',
                //     route: 'app.realUserMonitoring',
                // },
                // {
                //     icon: 'fa fa-gears',
                //     label: 'Administration',
                //     children: [
                //         {
                //             route: 'app.administration.visualisations',
                //             label: 'Visualisations',
                //         },
                //         {
                //             route: 'app.administration.reports',
                //             label: 'Reports',
                //         },
                //     ],
                // },
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
