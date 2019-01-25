(function (angular) {
    angular
        .module('leadwireApp')
        .factory('MenuFactory', [
            'Menus',
            '$state',
            function (Menus, $state) {
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
                {
                    route: 'app.billingList',
                    icon: 'fa fa-money',
                    label: 'Billing',
                },

            ],
            MANAGEMENT: [
                {
                    route: 'app.management.users',
                    icon: 'fa fa-user',
                    label: 'Manage Users',
                },
                {
                    route: 'app.management.plans',
                    icon: 'fa fa-money',
                    label: 'Manage plans',
                },
                {
                    route: 'app.management.applications',
                    icon: 'fa fa-desktop',
                    label: 'Manage applications',
                },
                {
                    route: 'app.management.applicationTypes',
                    icon: 'fa fa-desktop',
                    label: 'Manage types',
                },
            ],
        });
})(window.angular);
