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
            UserService.updatePaymentMethod(vm.billingInformation.card)
                .then(function(response) {
                    console.log(response);
                })
                .catch(function(error) {
                    console.log(error);
                });
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
        };
    }
})(window.angular, window.moment);
