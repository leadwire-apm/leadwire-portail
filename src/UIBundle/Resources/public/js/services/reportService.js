(function (angular) {
    angular
        .module('leadwireApp')
        .service('ReportService', [
            'ApplicationFactory',
            'MenuFactory',
            '$rootScope',
            '$localStorage',
            '$state',
            '$auth',
            'CONFIG',
            ReportServiceFN,
        ]);

    /**
     * Handle Report Logic
     *
     * @param ApplicationFactory
     * @param MenuFactory
     * @param $rootScope
     * @param $localStorage
     * @param $state
     * @constructor
     */
    function ReportServiceFN (
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
         * @param reports
         */
        service.updateSidebarMenus = function (reports) {
            //change sidebar menu using Menu factory
            $localStorage.currentMenu = MenuFactory.set(
                reports,
                function (menu) {
                    return menu.name;
                },
                function (menu) {
                    return $state.href('app.reports.manage', {
                        id: menu.id,
                        tenant: menu.tenant,
                    });
                },
                function (menu) {
                    return menu.icon || 'fa fa-cogs';
                },
            );
            $rootScope.menus = $localStorage.currentMenu;
            $localStorage.currentApplicationMenus = $localStorage.currentMenu;
        };

        service.getReport = function(tenant, reportId) {
            return CONFIG.KIBANA_BASE_URL + tenant + '?token=' + $auth.getToken() + '#/app/sentinl/' + reportId;
        };
        /**
         *
         * @param appId
         * @returns {Promise}
         */
        service.fetchReportsByAppId = function (appId) {
            return new Promise(function (resolve, reject) {
                ApplicationFactory.findMyReport(appId)
                    .then(function (response) {
                        $localStorage.reports = response.data.Default;
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
                            reports: response.data.Default,
                            custom: response.data.Custom,
                            path: 'app.reports.manage',
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
