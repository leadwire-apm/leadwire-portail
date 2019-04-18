(function (angular) {
    angular.module('leadwireApp')
        .controller('AddCompagnesController', [
            'TmecService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            '$stateParams',
            'ApplicationFactory',
            AddCompagnesCtrlFN,
        ])    
        .directive('myDatePicker', function () {
            return {
                restrict: 'A',
                require: '?ngModel',
                link: function (scope, element, attrs, ngModelController) {
          
                    // Private variables
                    var datepickerFormat = 'm/d/yyyy',
                        momentFormat = 'M/D/YYYY',
                        datepicker,
                        elPicker;
          
                    // Init date picker and get objects http://bootstrap-datepicker.readthedocs.org/en/release/index.html
                    datepicker = element.datepicker({
                        autoclose: true,
                        keyboardNavigation: false,
                        todayHighlight: true,
                        format: datepickerFormat
                    });
                    elPicker = datepicker.data('datepicker').picker;
          
                    // Adjust offset on show
                    datepicker.on('show', function (evt) {
                        elPicker.css('left', parseInt(elPicker.css('left')) + +attrs.offsetX);
                        elPicker.css('top', parseInt(elPicker.css('top')) + +attrs.offsetY);
                    });
          
                    // Only watch and format if ng-model is present https://docs.angularjs.org/api/ng/type/ngModel.NgModelController
                    if (ngModelController) {
                        // So we can maintain time
                        var lastModelValueMoment;
          
                        ngModelController.$formatters.push(function (modelValue) {
                            //
                            // Date -> String
                            //
          
                            // Get view value (String) from model value (Date)
                            var viewValue,
                                m = moment(modelValue);
                            if (modelValue && m.isValid()) {
                                // Valid date obj in model
                                lastModelValueMoment = m.clone(); // Save date (so we can restore time later)
                                viewValue = m.format(momentFormat);
                            } else {
                                // Invalid date obj in model
                                lastModelValueMoment = undefined;
                                viewValue = undefined;
                            }
          
                            // Update picker
                            element.datepicker('update', viewValue);
          
                            // Update view
                            return viewValue;
                        });
          
                        ngModelController.$parsers.push(function (viewValue) {
                            //
                            // String -> Date
                            //
          
                            // Get model value (Date) from view value (String)
                            var modelValue,
                                m = moment(viewValue, momentFormat, true);
                            if (viewValue && m.isValid()) {
                                // Valid date string in view
                                if (lastModelValueMoment) { // Restore time
                                    m.hour(lastModelValueMoment.hour());
                                    m.minute(lastModelValueMoment.minute());
                                    m.second(lastModelValueMoment.second());
                                    m.millisecond(lastModelValueMoment.millisecond());
                                }
                                modelValue = m.toDate();
                            } else {
                                // Invalid date string in view
                                modelValue = undefined;
                            }
          
                            // Update model
                            return modelValue;
                        });
          
                        datepicker.on('changeDate', function (evt) {
                            // Only update if it's NOT an <input> (if it's an <input> the datepicker plugin trys to cast the val to a Date)
                            if (evt.target.tagName !== 'INPUT') {
                                ngModelController.$setViewValue(moment(evt.date).format(momentFormat)); // $seViewValue basically calls the $parser above so we need to pass a string date value in
                                ngModelController.$render();
                            }
                        });
                    }
          
                }
            };
        });

    /**
     * Handle add new compagnes logic
     *
     */
    function AddCompagnesCtrlFN (
        TmecService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
        $stateParams,
        ApplicationFactory,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.save = function () {
           vm.flipActivityIndicator('isSaving');
          
           vm.applications.forEach(element => {
               if(element.id === vm.compagne.application){
                   vm.compagne.applicationName = element.name;
               }
           });

           TmecService.create(vm.compagne)
                .then(function () {
                    vm.flipActivityIndicator('isSaving');
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.tmecs');
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isSaving');
                    toastr.error(error.message || MESSAGES_CONSTANTS.ERROR);
                });
        };

        function loadApplications(){
            ApplicationFactory.findMyApplications()
            .then(function (applications) {
                vm.applications = applications.data;
            })
            .catch(function (error) {
            });
        }

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                },
                applications: [],
                compagne: {
                    version: '',
                    description: '',
                    startDate: '',
                    endDate: '',
                    application:'',
                    applicationName:''
                },
            });
            loadApplications();
        };
    }
})(window.angular);
