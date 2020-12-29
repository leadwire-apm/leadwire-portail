(function(angular) {
    angular
        .module('leadwireApp')
        .controller('privateReportsCtrl', [
            'DashboardService',
            '$sce',
            controller
        ]);

    function controller(DashboardService, $sce) {
        var vm = this;

        trustSrc = function (src) {
            return $sce.trustAsResourceUrl(src);
        }

        vm.reportLinkPrivate = trustSrc(DashboardService.getPrivateReport());

    }
})(window.angular);
