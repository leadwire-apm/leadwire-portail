(function (angular) {
    angular.module('leadwireApp')
        .service('AccessLevelService', [
            'AccessLevelFactory',
            '$rootScope',
            '$localStorage',
            'InvitationService',
            '$ocLazyLoad',
            '$modal',
            'FileService',
            'CONFIG',
            AccessLevelServiceFN,
        ]);

    function AccessLevelServiceFN(
        AccessLevelFactory,
        $rootScope,
        $localStorage,
        InvitationService,
        $ocLazyLoad,
        $modal,
        FileService,
        CONFIG,
    ) {

        var service = this;

        /**
         * set access level
         *
         * @param obj acl
         *
         * @return obj
         */
        service.setAccess = function (acl) {
            return AccessLevelFactory.setAccess(acl);
        }

        service.deleteAccess = function (acl) {
            return AccessLevelFactory.deleteAccess(acl);
        }
    }
})(window.angular);
