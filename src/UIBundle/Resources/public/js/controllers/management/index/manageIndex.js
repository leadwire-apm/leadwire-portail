(function (angular) {
    angular.module('leadwireApp')
        .controller('ManageIndexController', [
            '$sce',
            'CONFIG',
            ManagerIndexCtrlFN,
        ]);

    function ManagerIndexCtrlFN($sce, CONFIG) {
        var vm = this;
        vm.setIndexLink = $sce.trustAsResourceUrl(`${CONFIG.LEADWIRE_KIBANA_HOST}/app/opendistro_index_management_kibana#/indices?embed=true&from=0&search=&size=20&sortDirection=desc&sortField=index`);
    }
})(window.angular);
