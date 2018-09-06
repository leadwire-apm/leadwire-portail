(function(angular, moment) {
    angular
        .module('leadwireApp')
        .controller('billingListCtrl', ['UserService', 'CONFIG', controller]);

    function controller(UserService, CONSTANTS) {
        var vm = this;

        function loadSubscriptions() {
            vm.flipActivityIndicator();
            UserService.getSubscriptions()
                .then(function(response) {
                    vm.flipActivityIndicator();
                    vm.invoices = response.data.data;
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
            vm.loadSubscriptions = loadSubscriptions;

            vm.loadSubscriptions();
        }
    }
})(window.angular, window.moment);
