(function (angular) {
    angular.module('leadwireApp')
        .controller('ManageSecurityController', [
            '$sce',
            '$state',
            ManagerSecurityCtrlFN,
        ]);
    

    function ManagerSecurityCtrlFN($sce,$state) {
        const _list = [ "https://kibana.leadwire.io/app/security-configuration#/?embed=true",
        "https://kibana.leadwire.io/app/security-multitenancy#/?embed=true", 
        "https://kibana.leadwire.io/app/security-configuration#/rolesmapping?embed=true",
        "https://kibana.leadwire.io/app/security-configuration#/roles?embed=true",
        "https://kibana.leadwire.io/app/security-configuration#/actiongroups?embed=true",
        "https://kibana.leadwire.io/app/security-configuration#/tenants?embed=true",
        "https://kibana.leadwire.io/app/security-configuration#/internalusers?embed=true",
        "https://kibana.leadwire.io/app/security-configuration#/securityconfiguration?embed=true"];
        var vm = this;
            vm.setSecurityLink = $sce.trustAsResourceUrl(_list[0]);
    }
})(window.angular);
