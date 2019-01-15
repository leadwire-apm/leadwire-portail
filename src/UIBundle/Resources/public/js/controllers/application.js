(function(angular, swal) {
  angular
    .module('leadwireApp')
    .controller('applicationListCtrl', [
      '$scope',
      '$rootScope',
      'ApplicationFactory',
      'toastr',
      'MESSAGES_CONSTANTS',
      '$localStorage',
      'Paginator',
      '$modal',
      applicationListCtrlFN
    ]);

  function applicationListCtrlFN(
    $scope,
    $rootScope,
    ApplicationFactory,
    toastr,
    MESSAGES_CONSTANTS,
    $localStorage,
    Paginator,
    $modal
  ) {
    var vm = this;
    function getApps() {
      // get all
      vm.flipActivityIndicator('isLoading');
      ApplicationFactory.findAll()
        .then(function(response) {
          vm.flipActivityIndicator('isLoading');
          vm.apps = vm.paginator.items = response.data;
          $scope.$emit('set:apps', vm.apps);
        })
        .catch(function() {
          vm.flipActivityIndicator('isLoading');
          vm.apps = [];
          vm.paginator.items = vm.apps;
        });
    }

    vm.deleteApp = function(id) {
      swal(MESSAGES_CONSTANTS.SWEET_ALERT_DELETE_MODE).then(function(
        willDelete
      ) {
        if (willDelete) {
          ApplicationFactory.remove(id)
            .then(function() {
              vm.getApps();
              // $localStorage.applications = (
              //     $localStorage.applications ||
              //     ($localStorage.applications = [])
              // ).filter(function(currApp) {
              //     return currApp.id !== id;
              // });
              swal.close();
              toastr.success(MESSAGES_CONSTANTS.DELETE_APP_SUCCESS);
            })
            .catch(function(error) {
              swal.close();

              toastr.error(
                error.message ||
                  MESSAGES_CONSTANTS.DELETE_APP_FAILURE ||
                  MESSAGES_CONSTANTS.ERROR
              );
            });
        } else {
          swal('Your App is safe!');
        }
      });
    };

    vm.flipActivityIndicator = function(activity) {
      vm.ui[activity] = !vm.ui[activity];
    };

    vm.enableApp = function(selectedApp) {
      $modal.open({
        templateUrl: 'application/enable.html',
        controller: function($modalInstance, $state) {
          var modalVM = this;
          modalVM.enable = function() {
            ApplicationFactory.activate(selectedApp.id, modalVM.activationCode)
              .then(function(response) {
                if (response.data) {
                  toastr.success(MESSAGES_CONSTANTS.ACTIVATE_APP_SUCCESS);
                  var updatedApp = angular.extend(selectedApp, {
                    enabled: true
                  });
                  $scope.$emit('activate:app', updatedApp);
                  vm.apps = vm.apps.map(function(currentApp) {
                    return currentApp.id !== selectedApp.id
                      ? currentApp
                      : updatedApp;
                  });
                  $state.go('app.applicationDetail', {
                    id: selectedApp.id
                  });
                  $modalInstance.close();
                } else {
                  toastr.error(MESSAGES_CONSTANTS.ACTIVATE_APP_FAILURE);
                }
              })
              .catch(function(error) {
                toastr.error(
                  error.message ||
                    MESSAGES_CONSTANTS.EDIT_APP_FAILURE ||
                    MESSAGES_CONSTANTS.ERROR
                );
              });
          };
        },
        controllerAs: 'ctrl'
      });
    };

    vm.init = function() {
      vm = angular.extend(vm, {
        ui: {},
        paginator: Paginator.create({
          itemsPerPage: 5
        })
      });
      vm.getApps = getApps;
      vm.getApps();
    };
  }
})(window.angular, window.swal);
