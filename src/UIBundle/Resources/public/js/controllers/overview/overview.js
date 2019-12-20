(function (angular) {
    angular.module('leadwireApp')
        .controller('OverviewController', ['OverviewService', 'toastr', OverviewCtrlFN ]);

    /**
     * Handle clustyer stats
     *
     */
    function OverviewCtrlFN(OverviewService, toastr) {
        var vm = this;

        vm.msToTime = function(duration) {
            var milliseconds = parseInt((duration % 1000) / 100),
              seconds = Math.floor((duration / 1000) % 60),
              minutes = Math.floor((duration / (1000 * 60)) % 60),
              hours = Math.floor((duration / (1000 * 60 * 60)) % 24);
          
            hours = (hours < 10) ? "0" + hours : hours;
            minutes = (minutes < 10) ? "0" + minutes : minutes;
            seconds = (seconds < 10) ? "0" + seconds : seconds;
          
            return hours + ":" + minutes + ":" + seconds;
          }

         vm.bytesToSize = function(bytes) {
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            if (bytes == 0) return '0 Byte';
            var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
         }

        vm.load = function () {
            OverviewService.getClusterInformations()
            .then(function (stats) {
                vm.stats = stats;
                if(stats.status === "yellow") {
                    vm.border = "bg-warning";
                    vm.text = "text-warning"
                } else if(stats.status === "red"){
                    vm.border = "bg-danger";
                    vm.text = "text-danger"
                }else if(stats.status === "green"){
                    vm.border = "bg-success";
                    vm.text = "text-success"
                }
            })
            .catch(function (error) {
            });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                stats: {},
                border:"border-success",
                text: "text-success"
            });
            vm.load();
        };

        setInterval(function() {
            vm.init();
          }, 20000);
         
    }
})(window.angular);
