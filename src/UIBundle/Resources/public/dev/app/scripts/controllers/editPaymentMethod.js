(function(angular, moment) {
    angular
        .module('leadwireApp')
        .controller('editPaymentMethodCtrl', [
            '$scope',
            'UserService',
            '$rootScope',
            '$state',
            'toastr',
            'MESSAGES_CONSTANTS',
            'CONFIG',
            controller
        ]);

    function controller(
        $scope,
        UserService,
        $rootScope,
        $state,
        toastr,
        MESSAGES_CONSTANTS,
        CONSTANTS
    ) {
        var vm = this;

        function validateBilling() {
            vm.flipActivityIndicator('isSaving');
            UserService.updatePaymentMethod(vm.billingInformation.card)
                .then(function(response) {
                    vm.flipActivityIndicator('isSaving');
                    if (response.status === 200) {
                        toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                        $state.go('app.billingList');
                    } else {
                        handleError(response);
                    }
                })
                .catch(function(error) {
                    vm.flipActivityIndicator('isSaving');
                    toastr.error(error.message);
                });
        }
        function flipActivityIndicator(activity) {
            vm.ui[activity] = !vm.ui[activity];
        }
        function handleError(response) {
            if (
                response.data &&
                response.data.error &&
                response.data.error.exception &&
                response.data.error.exception.length
            ) {
                toastr.error(response.data.error.exception[0].message);
            } else {
                toastr.error(MESSAGES_CONSTANTS.ERROR);
            }
        }

        vm.onLoad = function() {
            vm = angular.extend(vm, {
                moment: moment,
                CONSTANTS: CONSTANTS,
                ui: {
                    isSaving: false
                },
                billingInformation: {
                    card: {
                        name: $rootScope.user.name
                    }
                }
            });
            vm.validateBilling = validateBilling;
            vm.flipActivityIndicator = flipActivityIndicator;
        };
    }
})(window.angular, window.moment);
