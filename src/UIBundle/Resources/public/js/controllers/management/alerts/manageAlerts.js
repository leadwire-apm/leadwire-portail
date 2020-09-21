(function (angular) {
    angular.module('leadwireApp')
        .controller('ManageAlertsController', [
            '$sce',
            'CONFIG',
            ManagerAlertCtrlFN,
        ]);

    function ManagerAlertCtrlFN($sce, CONFIG) {
        var vm = this;
        vm.setAlertLink = $sce.trustAsResourceUrl(`${CONFIG.LEADWIRE_KIBANA_HOST}/app/opendistro-alerting#/dashboard?embed=true&alertState=ALL&from=0&search=&severityLevel=ALL&size=20&sortDirection=desc&sortField=start_time`);
    }
})(window.angular);
