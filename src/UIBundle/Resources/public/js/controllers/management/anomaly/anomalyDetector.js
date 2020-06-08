(function (angular) {
    angular.module('leadwireApp')
        .controller('anomalyController', [
            '$sce',
            'CONFIG',
            ManagerAnomalyCtrlFN,
        ]);

    function ManagerAnomalyCtrlFN($sce, CONFIG) {
        var vm = this;
        vm.setAnomalyLink = $sce.trustAsResourceUrl(`${CONFIG.LEADWIRE_KIBANA_HOST}/app/opendistro-anomaly-detection-kibana#/dashboard?embed=true`);
    }
})(window.angular);
