(function(angular) {
    angular
        .module('leadwireApp')
        .service('DashboardService', [
            'ApplicationFactory',
            'MenuFactory',
            '$rootScope',
            '$localStorage',
            '$state',
            DashboardServiceFN
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
        $state
    ) {
        var service = this;

        function updateSidebarMenus(defaultDashboards) {
            $localStorage.currentMenu = MenuFactory.set(
                defaultDashboards,
                function(menu) {
                    return menu['name'];
                },
                function(menu) {
                    return $state.href('app.dashboard.home', {
                        id: menu.id
                    });
                },
                function(menu) {
                    return menu.icon || 'fa fa-dashboard';
                }
            );
            $rootScope.menus = $localStorage.currentMenu;
        }

        service.fetchDashboardsByAppId = function(appId) {
            return new Promise(function(resolve, reject) {
                ApplicationFactory.findMyDashboard(appId)
                    .then(function(response) {
                        $localStorage.dashboards = response.data.Default;
                        $rootScope.$broadcast('set:contextApp', appId);
                        $rootScope.$broadcast('set:customMenus', {
                            withCustom: !!Object.keys(
                                response.data.Custom || {}
                            ).length,
                            list: response.data.Custom || {}
                        });
                        updateSidebarMenus(response.data.Default);
                        resolve({
                            appId: appId,
                            dashboards: response.data.Default
                        });
                    })
                    .catch(function(error) {
                        console.log('Error', error);
                        reject(error);
                    });
            });
        };
    }
})(window.angular);
