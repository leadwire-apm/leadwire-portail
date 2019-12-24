(function (angular) {
    angular.module('leadwireApp')
        .controller('OverviewController', ['OverviewService', 'toastr', OverviewCtrlFN]);

    /**
     * Handle clustyer stats
     *
     */
    function OverviewCtrlFN(OverviewService, toastr) {
        var vm = this;

        vm.msToTime = function (duration) {
            var minutes = Math.floor((duration / (1000 * 60)) % 60),
                hours = Math.floor((duration / (1000 * 60 * 60)) % 24);

            hours = (hours < 10) ? "0" + hours : hours;
            minutes = (minutes < 10) ? "0" + minutes : minutes;

            return hours + ":" + minutes;
        }

        vm.bytesToSize = function (bytes) {
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            if (bytes == 0) return '0 Byte';
            var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            var x = bytes / Math.pow(1024, i);
            return x.toFixed(1) + ' ' + sizes[i];
        }

        vm.load = function () {
            OverviewService.getClusterInformations()
                .then(function (data) {
                    vm.nodes.forEach(element => {
                        data.nodes.forEach(node => {
                            if (element.nodeName === node.nodeName && element.isOpen === true) {
                                node.isOpen = element.isOpen;
                            }
                        })
                    });
                    vm.nodes = data.nodes;
                    vm.cluster = data.cluster;
                })
                .catch(function (error) {
                });
        };

        vm.getColor = function (statu) {

            if (statu === "yellow") {
                return "bg-warning";
            } else if (statu === "red") {
                return "bg-danger";
            } else if (statu === "green") {
                return "bg-success";
            }
        }

        vm.isMasterNode = function (node){
            return (node.roles.indexOf("master") > -1);
        }

        vm.isDataNode = function (node){
            return (node.roles.indexOf("data")  > -1);
        }


        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                nodes: [],
                cluster: {},
                border: "border-success"
            });
            vm.load();
        };

        setInterval(function () {
            vm.load();
        }, 20000);

    }
})(window.angular);
