(function(angular, moment) {
    angular
        .module('leadwireApp')
        .controller('billingListCtrl', ['UserService','$rootScope', 'CONFIG', controller]);

    function controller(UserService,$rootScope, PlanFactory,CONSTANTS) {
        var vm = this;
        function loadSubscription() {
            UserService.getSubscription()
                .then(function(response) {
                    vm.subscription = response.data;
                })
                .catch(function() {});
        }

        function loadInvoices() {
            vm.flipActivityIndicator();
            UserService.getInvoices()
                .then(function(response) {
                    vm.flipActivityIndicator();
                    vm.invoices = response.data;
                })
                .catch(function() {
                    vm.flipActivityIndicator();
                });
        }

        function flipActivityIndicator() {
            vm.ui.isLoading = !vm.ui.isLoading;
        }

        vm.onLoad = function() {
            vm = angular.extend(vm, {
                moment: moment,
                CONSTANTS: CONSTANTS,
                ui: {}
            });
            vm.flipActivityIndicator = flipActivityIndicator;
            vm.loadInvoices = loadInvoices;
            vm.loadSubscription = loadSubscription;
            vm.loadInvoices();
            vm.loadSubscription();
        };
    }
})(window.angular, window.moment);
