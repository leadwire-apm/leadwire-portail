(function (angular) {
    angular.module('leadwireApp')
        .controller('ManageIndexController', [
            '$sce',
            ManagerIndexCtrlFN,
        ]);

    function ManagerIndexCtrlFN($sce) {
        var vm = this;
        vm.setIndexLink = $sce.trustAsResourceUrl("https://kibana.leadwire.io/app/opendistro_index_management_kibana#/indices?embed=true&from=0&search=&size=20&sortDirection=desc&sortField=index");
    }
})(window.angular);
