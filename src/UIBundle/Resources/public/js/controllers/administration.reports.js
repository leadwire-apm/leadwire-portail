(function(angular) {
    angular
    .module('leadwireApp')
    .controller('administrationReportsController', ['$sce','ConfigService', controller]);

    function controller($sce, ConfigService) {
        var ctrl = this;
        ctrl.link = $sce.trustAsResourceUrl(ConfigService.getUrl("app/kibana#/dashboards?_g=()",true));
    }


})(window.angular);