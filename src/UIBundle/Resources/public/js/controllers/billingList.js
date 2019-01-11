(function(angular, moment) {
    angular
        .module('leadwireApp')
        .controller('billingListCtrl', [
            '$scope',
            'UserService',
            'PlanFactory',
            '$rootScope',
            'CONFIG',
            billingListCtrlFN
        ]);

    function billingListCtrlFN(
        $scope,
        UserService,
        PlanFactory,
        $rootScope,
        CONSTANTS
    ) {
        var vm = this;
        function loadSubscription() {
            UserService.getSubscription()
                .then(function(response) {
                    if (response.status === 200) {
                        vm.subscription = response.data;
                    }
                })
                .catch(function() {});
        }

        function loadInvoices() {
            vm.flipActivityIndicator();
            UserService.getInvoices()
                .then(function(response) {
                    vm.flipActivityIndicator();
                    if (response.status === 200) {
                        vm.invoices = response.data;
                    }
                })
                .catch(function() {
                    vm.flipActivityIndicator();
                });
        }

        function loadPlans() {
            PlanFactory.findAll().then(function(response) {
                vm.plans = response.data;
            });
        }

        function flipActivityIndicator() {
            vm.ui.isLoading = !vm.ui.isLoading;
        }

        function updateCantUpgrade(newVal) {
            var cant = true;
            if (newVal && newVal.length) {
                var currentPlanPrice = $rootScope.user.plan.price;

                newVal.forEach(function(plan) {
                    if (cant && plan.price > currentPlanPrice) {
                        cant = false;
                    }
                });
            }
            vm.ui.showUpgrade = !cant;
        }

        function registerWatchers() {
            $scope.$watch(function() {
                return vm.plans;
            }, updateCantUpgrade);
        }


        vm.onLoad = function() {
            vm = angular.extend(vm, {
                moment: moment,
                CONSTANTS: CONSTANTS,
                ui: {
                    showUpgrade:true
                }
            });
            vm.flipActivityIndicator = flipActivityIndicator;
            vm.loadInvoices = loadInvoices;
            vm.loadSubscription = loadSubscription;
            vm.loadPlans = loadPlans;
            vm.loadInvoices();
            vm.loadSubscription();
            vm.loadPlans();
            registerWatchers();

        };
    }
})(window.angular, window.moment);
