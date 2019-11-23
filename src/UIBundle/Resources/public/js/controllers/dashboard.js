(function(angular) {
    angular.module('leadwireApp').controller('dashboardCtrl', [
        '$sce',
        '$scope',
        '$rootScope',
        'DashboardService',
        '$localStorage',
        '$state',
        '$stateParams',
        dashboardCtrl,
    ]);

    function dashboardCtrl($sce, $scope, $rootScope, DashboardService, $localStorage, $state, $stateParams) {
        var vm = this;
        vm.applications = $localStorage.applications;
        vm.dashboardLink = DashboardService.getDashboard($state.params.tenant, $state.params.id);

        vm.onLoad = function() {
            vm.isLoading = true;
            $rootScope.menus = $localStorage.currentApplicationMenus;
            $scope.$watch(
                function () {
                    $el = document.querySelector('#L' + $stateParams.id.replace(/-/g,""));
                    return $el;
                },
                function (newValue, oldValue) {
                    if (newValue != null) {
                        newValue.parentNode.parentNode.parentNode.childNodes[1].querySelector('a').click();
                    }
                }
            );
        };

        $scope.trustSrc = function(src) {
            return $sce.trustAsResourceUrl(src);
        }

        vm.onLoad();
    }
})(window.angular);
