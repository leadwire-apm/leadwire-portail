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
            CodeService.copyToClipboard(code.code);
            toastr.info(MESSAGES_CONSTANTS.CODE_COPIED);
        };

        vm.generateCode = function () {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willGenerate) {
                    if (willGenerate) {
                        CodeService.create()
                            .then(function (code) {
                                swal('Code generated successfully!', code);
                                vm.fetchCodes;
                            })
                            .catch(function () {
                                toastr.error(MESSAGES_CONSTANTS.ERROR);
                            });
                    } else {
                        swal.close();
                    }
                });
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
