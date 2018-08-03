angular.module('leadwireApp').factory('MenuFactory', function(Menus) {
    return {
        get: function(menuKey) {
            return Menus[menuKey];
        },

    };
});

angular.module('leadwireApp').constant('Menus', {
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
        },
        {
            icon: 'fa fa-table',
            label: 'Data Browser',
        },
        {
            icon: 'fa fa-briefcase',
            label: 'Business Transactions',
        },
        {
            icon: 'fa fa-sitemap',
            label: 'Architecture Discovery',
        },
        {
            icon: 'fa fa-exclamation-triangle',
            label: 'Alerts',
        },
        {
            icon: 'fa fa-book',
            label: 'Documentation',
        },
        {
            icon: 'fa fa-support',
            label: 'Support',
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
});


