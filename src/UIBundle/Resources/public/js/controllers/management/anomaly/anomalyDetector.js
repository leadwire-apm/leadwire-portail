(function (angular) {
    angular.module('leadwireApp')
        .controller('anomalyController', [
            '$sce',
            ManagerAnomalyCtrlFN,
        ]);

    function ManagerAnomalyCtrlFN($sce) {
        var vm = this;
        vm.setAnomalyLink = $sce.trustAsResourceUrl("https://kibana.leadwire.io/app/opendistro-anomaly-detection#/detectors?embed=true");
    }
})(window.angular);
