(function (angular) {
    angular
        .module('leadwireApp')
        .factory('MenuFactory', [
            'Menus',
            '$state',
            'CONFIG',
            'UserService',
            '$localStorage',
            function (Menus, $state, CONFIG, UserService, $localStorage) {

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

                if(CONFIG.LOGIN_METHOD === 'github'){
                    Menus.MANAGEMENT.push(
                        {
                            route: 'app.management.codes',
                            abstractRoute: 'app.management',
                            icon: 'fa fa-qrcode',
                            label: 'Activation codes',
                        }
                    )
                }

                return {
                    update : function(){

                    },
                    get: function (menuKey) {
                        var menus = [];
                        if (menuKey in Menus) {
                            menus = Menus[menuKey].map(function (menu) {
                                return angular.extend({}, menu, {
                                    route: $state.href(menu.route),
                                });
                            });
                        }
                        if(menuKey === "CAMPAGNE"){

                            if(CONFIG.COMPAGNE_ENABLED === true){
                                if (UserService.isAdmin($localStorage.user)) {
                                    menus.push(
                                        {
                                            route:  $state.href('app.management.tmecs'),
                                            abstractRoute: 'app.management',
                                            icon: 'fa fa-table',
                                            label: 'Manage Campaigns',
                                        },
                                    );
                                } else {
                                    menus.push(
                                        {
                                            route:  $state.href('app.tmecs'),
                                            icon: 'fa fa-table',
                                            label: 'Campaigns',
                                        }
                                    )
                                }
                                menus.push({
                                    url: CONFIG.JENKINS_URL,
                                    icon: 'fa fa-play-circle',
                                    label: 'Launch',
                                    external:true
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
                    ) {
                        try {
                            newMenus = Object.keys(menus);

                            newMenus.forEach(function(theme) {
                                sub = menus[theme].map(function (menu) {
                                    return {
                                        label: labelCallback(menu),
                                        route: routeCallback(menu),
                                        icon: iconCallback(menu),
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
               CAMPAGNE:  [
                {
                    route: 'app.overview',
                    icon: 'fa fa-paper-plane',
                    label: 'Overview',
                }
            ],
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
            ],
        });
})(window.angular);
