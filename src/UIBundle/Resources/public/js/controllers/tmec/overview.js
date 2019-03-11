(function (angular) {
    angular.module('leadwireApp')
        .controller('TmecOverviewController', [
            'TmecService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$stateParams',
            '$modal',
            OverviewControllerFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function OverviewControllerFN (
        TmecService,
        toastr,
        MESSAGES_CONSTANTS,
        $stateParams,
        $modal,
    ) {
        var vm = this;
    }
})(window.angular);
