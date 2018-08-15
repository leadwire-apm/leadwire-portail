angular
    .module('leadwireApp')
    .service('DashboardService', function(
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
    });
