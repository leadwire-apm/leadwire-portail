(function (angular) {
    angular
        .module('leadwireApp')
        .run(function ($rootScope,
                       $localStorage,
                       CONFIG, $templateCache,
                       $state,
                       MenuFactory,
                       $location,
                       iFrameService) {
            // $rootScope.menus = $localStorage.currentMenu;
            $rootScope.applications = $localStorage.applications;
            $rootScope.dashboards = $localStorage.dashboards;
            $rootScope.ASSETS_BASE_URL = CONFIG.ASSETS_BASE_URL;
            $rootScope.DOWNLOAD_URL = CONFIG.DOWNLOAD_URL;
            $rootScope.UPLOAD_URL = CONFIG.UPLOAD_URL;
            $rootScope.$watch('applications', function (newVal) {
                $localStorage.applications = newVal;
            });
            $state.defaultErrorHandler(function (error) {
// This is a naive example of how to silence the default error handler.
                if (error.detail === 'UNAUTHORIZED') {
                    $rootScope.menus = MenuFactory.get('SETTINGS');
                    $location.path('/applications/list');
                }
            });

            $templateCache.put(
                'template/tabs/tabset.html',
                '\n' +
                '<div>\n' +
                '  <ul class="nav nav-{{type || \'tabs\'}}" ng-class="{\'nav-stacked\': vertical, \'nav-justified\': justified}" ng-transclude></ul>\n' +
                '  <div class="tab-content">\n' +
                //This is the part that needs to be added. NOTE that the ng-repeat was removed from the original div
                //and replaced with the ng-repeat-start and ng-repeat-end directives. This is you aren't limited to
                //outputting a single dom node for each tab. We need two, one for the accordion header and one for the tab itself
                '    <div ng-repeat-start="tab in tabs" ng-click="tab.active=true" class="tab-accordion-header" ng-class="{\'active\': tab.active}">{{tab.heading}}</div>\n' +
                '    <div class="tab-pane" \n' +
                '         ng-repeat-end \n' +
                '         ng-class="{active: tab.active}"\n' +
                '         tab-content-transclude="tab">\n' +
                '    </div>\n' +
                '  </div>\n' +
                '</div>\n' +
                '',
            );

            $rootScope.$on("$locationChangeSuccess", function (event) {
                iFrameService.resetDimensions($('iframe'));
            });

            $(window).on('resize', function() {
                if ($('body').hasClass('iframe')) {
                    iFrameService.setDimensions($('iframe'));
                }
            });
        });

})(window.angular);
