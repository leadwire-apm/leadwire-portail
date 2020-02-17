(function (angular) {
    angular
        .module('leadwireApp')
        .service('DashboardService', [
            'ApplicationFactory',
            'MenuFactory',
            '$rootScope',
            '$localStorage',
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
     * @param $localStorage
     * @param $state
     * @constructor
     */
    function DashboardServiceFN(
        ApplicationFactory,
        MenuFactory,
        $rootScope,
        $localStorage,
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
            $localStorage.currentMenu = MenuFactory.set(
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
            $rootScope.menus = $localStorage.currentMenu;
            $localStorage.currentApplicationMenus = $localStorage.currentMenu;
        };

        service.getDashboard = function (dashboardId, tenant) {
            var index = $localStorage.selectedApp.applicationIndex;
            if(tenant.indexOf('shared') > -1) {
                index = $localStorage.selectedApp.sharedIndex;
            }
            var tenant = $localStorage.selectedEnv.name + "-" + index
            return CONFIG.KIBANA_BASE_URL + "app/kibana?security_tenant=" + tenant + '#/dashboard/' + dashboardId;
        };
        /**
         *
         * @param appId
         * @returns {Promise}
         */
        service.fetchDashboardsByAppId = function (appId) {
            return new Promise(function (resolve, reject) {
                ApplicationFactory.findMyDashboard(appId, $localStorage.selectedEnv.name)
                    .then(function (response) {
                        $localStorage.dashboards = response.data.Default;
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
                ApplicationFactory.findMyDashboard(appId, $localStorage.selectedEnv.name)
                    .then(function (response) {
                        resolve(response.data.Default);
                    })
                    .catch(function (error) {
                        console.log('Error', error);
                        reject(error);
                    });
            });
        };
    }
})(window.angular);
