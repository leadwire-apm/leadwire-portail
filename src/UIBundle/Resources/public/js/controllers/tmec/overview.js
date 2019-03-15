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

        vm.getClass = function (steps, step) {

            var label = "label label-danger";

            steps.forEach(st => {
                if (st.current === true && st.order >= step.order  || step.order === 1) {
                    label = "label label-success";
                }
            });

            if (step.waiting) {
                label = "label label-warning";
            } 

            return label;
        }

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };


        vm.loadApplications = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            TmecService.all()
                .then(function (applications) {
                    vm.flipActivityIndicator('isLoading');
                    vm.applications = applications;
                    console.log(applications)

                    getCompagnes();
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                    vm.applications = [];
                });
        };

        var getCompagnes = function () {
            vm.applications.forEach(application => {
                TmecService.list({ "application": application.id, "completed": false })
                    .then(function (compagnes) {
                        compagnes.forEach(compagne => {
                            getSteps(compagne.id, function (steps) {
                                compagne.steps = steps;
                            })
                        });
                        application.compagnes = compagnes;
                        vm.flipActivityIndicator('isLoading');
                    })
                    .catch(function (error) {
                        vm.flipActivityIndicator('isLoading');
                    });
            });
        }

        var getSteps = function (compagneId, cb) {
            TmecService.listSteps(compagneId)
                .then(function (steps) {
                    cb(steps);
                })
                .catch(function (error) {
                    cb([]);
                });
        }

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isLoading: false
                },
                applications: [],
            });
            vm.loadApplications();
        }
    }
setTimeout(() => {
    $(document).ready(function() {
        //toggle the component with class accordion_body
        $(".accordion_head").click(function() {
          if ($('.accordion_body').is(':visible')) {
            $(".accordion_body").slideUp(300);
            $(".plusminus").text('+');
          }
          if ($(this).next(".accordion_body").is(':visible')) {
            $(this).next(".accordion_body").slideUp(300);
            $(this).children(".plusminus").text('+');
          } else {
            $(this).next(".accordion_body").slideDown(300);
            $(this).children(".plusminus").text('-');
          }
        });
      });
}, 800);
   
      
})(window.angular);
