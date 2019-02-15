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
            vm.flipActivityIndicator('isLoading');
            CodeService.list()
                .then(function (codes) {
                    vm.codes = codes;
                    vm.flipActivityIndicator('isLoading');
                })
                .catch(function (err) {
                    // todo
                    vm.flipActivityIndicator('isLoading');
                });
        };

        vm.copyCode = function (code) {
            // console.log(code);
            CodeService.copyToClipboard(code.code);
            toastr.info(MESSAGES_CONSTANTS.CODE_COPIED);
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
