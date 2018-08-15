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

        service.handleAfterRedirect = function(connectedUser) {
            return new Promise(function(resolve, reject) {
                if (connectedUser.defaultApp) {
                    ApplicationFactory.findMyDashboard(
                        connectedUser.defaultApp.id
                    )
                        .then(function(response) {
                            $localStorage.currentMenu = MenuFactory.set(
                                response.data,
                                function(menu) {
                                    return menu['name'];
                                },
                                function(menu) {
                                    return $state.href("app.dashboard",{id:menu.id});
                                },
                                function(menu) {
                                    return menu.icon || 'fa fa-dashboard';
                                }
                            );
                            $rootScope.menus = $localStorage.currentMenu;
                            resolve(connectedUser.defaultApp.id);
                        })
                        .catch(function(error) {
                            reject(error);
                        });
                }
            });
        };
    });
