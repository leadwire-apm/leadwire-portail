(function (angular) {
    angular.module('leadwireApp')
        .controller('ManageSecurityController', [
            '$sce',
            'CONFIG',
            ManagerSecurityCtrlFN,
        ]);
    

    function ManagerSecurityCtrlFN($sce, CONFIG) {
        const _list = [ 
            `${CONFIG.LEADWIRE_KIBANA_HOST}/app/opendistro_security#/app/opendistro_security/getstarted?embed=true`,
            `${CONFIG.LEADWIRE_KIBANA_HOST}/app/security-multitenancy#/?embed=true`, 
            `${CONFIG.LEADWIRE_KIBANA_HOST}/app/security-configuration#/rolesmapping?embed=true`,
            `${CONFIG.LEADWIRE_KIBANA_HOST}/app/security-configuration#/roles?embed=true`,
            `${CONFIG.LEADWIRE_KIBANA_HOST}/app/security-configuration#/actiongroups?embed=true`,
            `${CONFIG.LEADWIRE_KIBANA_HOST}/app/security-configuration#/tenants?embed=true`,
            `${CONFIG.LEADWIRE_KIBANA_HOST}/app/security-configuration#/internalusers?embed=true`,
            `${CONFIG.LEADWIRE_KIBANA_HOST}/app/security-configuration#/securityconfiguration?embed=true`];
        var vm = this;
            vm.setSecurityLink = $sce.trustAsResourceUrl(_list[0]);
    }
})(window.angular);
