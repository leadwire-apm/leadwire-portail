(function (angular) {
    angular.module('leadwireApp')
        .controller('AddWatcherCtrl', ['$modalInstance', AddWatcherCtrl]);

    /**
     * Handle clustyer stats
     *
     */
    function AddWatcherCtrl($modalInstance) {
        var vm = this;
    
        vm.watcher = {'subject': 'Lead Wire Website Report'};

        vm.ok = function () {
            $modalInstance.close("Ok");
        }

        vm.cancel = function () {
            $modalInstance.dismiss();
        }
    }
})(window.angular);
