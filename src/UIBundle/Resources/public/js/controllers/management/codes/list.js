(function (angular) {
    angular.module('leadwireApp')
        .controller('ListCodeController', [
            'CodeService',
            'toastr',
            'CONFIG',
            'MESSAGES_CONSTANTS',
            '$state',
            ListCodeCtrlFN,
        ]);

    /**
     * Handle add new template logic
     *
     */
    function ListCodeCtrlFN (
        CodeService,
        toastr,
        CONSTANTS,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.fetchCodes = function () {
            CodeService.list()
                .then(function (codes) {
                    vm.codes = codes;
                })
                .catch(function (err) {
                    // todo
                });
        };

        vm.copyCode = function (code) {
            // console.log(code);
            CodeService.copyToClipboard(code);
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isLoading: false,
                },
                codes: [],
            });
            vm.fetchCodes();
        };

    }
})(window.angular);
