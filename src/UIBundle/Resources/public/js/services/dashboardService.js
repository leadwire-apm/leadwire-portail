(function (angular) {
    angular
        .module('leadwireApp')
        .service('DashboardService', [
            'ApplicationFactory',
            'MenuFactory',
            '$rootScope',
            '$sessionStorage',
            '$state',
            '$auth',
            'CONFIG',
            DashboardServiceFN,
        ]);

    /**
     * Handle Dashboard Logic
     *
     * @param ApplicationFactory
     * @param MenuFactory
     * @param $rootScope
     * @param $sessionStorage
     * @param $state
     * @constructor
     */
    function DashboardServiceFN(
        ApplicationFactory,
        MenuFactory,
        $rootScope,
        $sessionStorage,
        $state,
        $auth,
        CONFIG,
    ) {
        var service = this;

        /**
         *
         * @param dashboards
         */
        service.updateSidebarMenus = function (dashboards) {
            //change sidebar menu using Menu factory
            $sessionStorage.currentMenu = MenuFactory.set(
                dashboards,
                function (menu) {
                    return menu.name;
                },
                function (menu) {
                    return $state.href('app.dashboard.home', {
                        id: menu.id,
                        tenant: menu.tenant,
                    });
                },
                function (menu) {
                    return menu.icon || 'fa fa-tachometer-alt';
                },
                function (menu) {
                    return menu.visible;
                },
                function (menu) {
                    return 'app.dashboard.home({id:"' + menu.id + '",tenant:"' + menu.tenant + '"})';
                },
                function (menu) {
                    return {
                        id: menu.id,
                        tenant: menu.tenant
                    }
                },
                function (menu) {
                    return "L" + menu.id.replace(/-/g, "");
                }
            );
            $rootScope.menus = $sessionStorage.currentMenu;
            $sessionStorage.currentApplicationMenus = $sessionStorage.currentMenu;
        };

        service.getDashboard = function (dashboardId, tenant) {
            var index = $sessionStorage.selectedApp.applicationIndex;
            if(tenant.indexOf('shared') > -1) {
                index = $sessionStorage.selectedApp.sharedIndex;
            }
            var tenant = $sessionStorage.selectedEnv.name + "-" + index
            return CONFIG.KIBANA_BASE_URL + "app/kibana?security_tenant=" + tenant + '#/dashboard/' + dashboardId;
        };
        /**
         *
         * @param appId
         * @returns {Promise}
         */
        service.fetchDashboardsByAppId = function (appId) {
            return new Promise(function (resolve, reject) {
                ApplicationFactory.findMyDashboard(appId, $sessionStorage.selectedEnv.name)
                    .then(function (response) {
                        $sessionStorage.dashboards = response.data.Default;
                        //inform other controller that we changed context
                        $rootScope.$broadcast('set:contextApp', appId);
                        $rootScope.$broadcast('set:customMenus', {
                            withCustom: !!Object.keys(
                                response.data.Custom || {},
                            ).length,
                            list: response.data.Custom || {},
                        });
                        service.updateSidebarMenus(response.data.Default);
                        resolve({
                            appId: appId,
                            dashboards: response.data.Default,
                            custom: response.data.Custom,
                            path: 'app.dashboard.home',
                        });
                    })
                    .catch(function (error) {
                        console.log('Error', error);
                        reject(error);
                    });
            });
        };

        /**
         *
         * @param appId
         * @returns {Promise}
         */
        service.fetchDashboardsListByAppId = function (appId) {
            return new Promise(function (resolve, reject) {
                ApplicationFactory.findMyDashboard(appId, $sessionStorage.selectedEnv.name)
                    .then(function (response) {
                        resolve(response.data.Default);
                    })
                    .catch(function (error) {
                        console.log('Error', error);
                        reject(error);
                    });
            });
        };

        /**
         *
         * @param appId
         * @returns {Promise}
         */
        service.fetchDashboardsAllListByAppId = function (appId) {
            return new Promise(function (resolve, reject) {
                ApplicationFactory.findMyDashboard(appId, $sessionStorage.selectedEnv.name)
                    .then(function (response) {
                        resolve(response.data);
                    })
                    .catch(function (error) {
                        console.log('Error', error);
                        reject(error);
                    });
            });
        };
    }
})(window.angular);
