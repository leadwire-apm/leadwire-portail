(function (angular) {
    angular.module('leadwireApp')
        .controller('TmecOverviewController', [
            'ApplicationService',
            'TmecService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$stateParams',
            '$modal',
            CtrlOverviewControllerFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function CtrlOverviewControllerFN(
        ApplicationService,
        TmecService,
        toastr,
        MESSAGES_CONSTANTS,
        $stateParams,
        $modal,
    ) {

        var vm = this;
        var margins = {
            top: 70,
            bottom: 40,
            left: 30,
            width: 550
          };

        vm.generatePdf = function () {
            var pdf = new jsPDF('p', 'pt', 'a4');
            pdf.setFontSize(18);
            pdf.fromHTML(document.getElementById('panel'),
                margins.left, // x coord
                margins.top,
                {
                    // y coord
                    width: margins.width// max width of content on PDF
                }, function (dispose) {
                    pdf.save('overview.pdf');
                },
                margins);
        }

        vm.getClass = function (step) {

            var label = "label label-danger";

            if (step.completed) {
                label = "label label-success";
            }

            if (step.current) {
                label = "label label-primary";
            }

            if (step.waiting) {
                label = "label label-warning";
            }

            return label;
        }

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        getAllApplications = function (cb) {
            TmecService.all()
                .then(function (applications) {
                    var appIds = [];
                    applications.forEach(application => {
                        appIds.push(application.id)
                    });
                    cb(appIds)
                })
                .catch(function (error) {
                });
        }


        vm.loadApplications = function () {
            vm.flipActivityIndicator('isLoading');

            getAllApplications(function(appIds){
                TmecService.list({ "completed": vm.all, "ids": appIds })
                .then(function (compagnes) {
                    vm.flipActivityIndicator('isLoading');
                    vm.compagnes = compagnes;
                    console.log(compagnes)
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                });
            })
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isLoading: false
                },
                compagnes: [],
                all: false
            });
            vm.loadApplications();
        }
    }
})(window.angular);
