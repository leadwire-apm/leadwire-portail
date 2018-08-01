angular.module('leadwireApp')
  .factory('Account', function ($http) {
    return {
      getProfile: function () {
        return $http.get('http://localhost:9000/api/user/me');
      },
      updateProfile: function (profileData) {
        return $http.put('http://localhost:9000/api/user/' + profileData.id + '/update', profileData);
      }
    };
  })
  .service('User', ['Account', '$rootScope', '$localStorage', function (Account, $rootScope, $localStorage) {

    this.getProfile = function () {
      if (angular.isUndefined($localStorage.user) || $localStorage.user === null) {
        Account.getProfile().then(function (response) {
          this.informations = response.data;
          this.informations.fname = response.data.login;
          if (angular.isDefined(response.data.displayName) && response.data.displayName !== null) {
            this.informations.fname = response.data.displayName;
          }
          this.informations.avatar = response.data.avatar;
          $localStorage.user = this.informations
          $rootScope.$broadcast('user:updated', this.informations);
        }).catch(function (response) {
          //toastr.error(response.data.message, response.status);
        });
      } else {
        this.informations = $localStorage.user;
      }

    }
  }]);