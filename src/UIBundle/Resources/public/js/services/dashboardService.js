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
    function DashboardServiceFN (
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
                        tenant: null,
                    });
                },
                function (menu) {
                    return menu.icon || 'fa fa-dashboard';
                },
            );
            $rootScope.menus = $localStorage.currentMenu;
            // $rootScope.menus = MenuFactory.get('DASHBOARD');
        };

        service.getDashboard = function(tenant, dashboardId) {
            return CONFIG.KIBANA_BASE_URL + tenant + '?token=' + $auth.getToken() + '#/dashboard/' + dashboardId;
        };
        /**
         *
         * @param appId
         * @returns {Promise}
         */
        service.fetchDashboardsByAppId = function (appId) {
            return new Promise(function (resolve, reject) {
                ApplicationFactory.findMyDashboard(appId)
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
    }
})(window.angular);
