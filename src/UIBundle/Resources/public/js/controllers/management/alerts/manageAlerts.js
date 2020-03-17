(function (angular) {
    angular.module('leadwireApp')
        .controller('ManageAlertsController', [
            '$sce',
            ManagerAlertCtrlFN,
        ]);

    function ManagerAlertCtrlFN($sce) {
        var vm = this;
        vm.setAlertLink = $sce.trustAsResourceUrl("https://kibana.leadwire.io/app/opendistro-alerting#/dashboard?embed=true&alertState=ALL&from=0&search=&severityLevel=ALL&size=20&sortDirection=desc&sortField=start_time");
    }
})(window.angular);
