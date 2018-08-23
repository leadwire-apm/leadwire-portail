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

        service.fetchDashboardsByAppId = function(appId) {
            return new Promise(function(resolve, reject) {
                ApplicationFactory.findMyDashboard(appId)
                    .then(function(response) {
                        $localStorage.dashboards = response.data;
                        $localStorage.selectedAppId = appId;
                        $localStorage.currentMenu = MenuFactory.set(
                            response.data,
                            function(menu) {
                                return menu['name'];
                            },
                            function(menu) {
                                return $state.href('app.dashboard', {
                                    id: menu.id
                                });
                            },
                            function(menu) {
                                return menu.icon || 'fa fa-dashboard';
                            }
                        );
                        $rootScope.menus = $localStorage.currentMenu;
                        resolve(appId);
                    })
                    .catch(function(error) {
                        reject(error);
                    });
            });
        };
    }
})(window.angular);
