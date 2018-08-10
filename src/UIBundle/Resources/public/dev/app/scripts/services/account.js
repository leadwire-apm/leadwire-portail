angular.module('leadwireApp').factory('Account', function($http, CONFIG) {
    return {
        getProfile: function() {
            return $http.get(CONFIG.BASE_URL + 'api/user/me');
        },
        updateProfile: function(profileData) {
            return $http.put(
                CONFIG.BASE_URL + 'api/user/' + profileData.id + '/update',
                profileData);
        },
    };
}).service('UserService', [
    'Account',
    '$rootScope',
    '$localStorage',
    'Invitation',
    function(Account, $rootScope, $localStorage, Invitation) {

        var service = this;
        service.setProfile = function() {
            return new Promise(function(resolve, reject) {
                if (angular.isUndefined($localStorage.user) ||
                    $localStorage.user === null) {
                    Account.getProfile().then(function(response) {
                        var userInfo = response.data;
                        userInfo.fname = response.data.login;
                        if (angular.isDefined(response.data.displayName) &&
                            response.data.displayName !== null) {
                            userInfo.fname = response.data.displayName;
                        }

                        userInfo.avatar = response.data.avatar;
                        $localStorage.user = userInfo;
                        $rootScope.$broadcast('user:updated', userInfo);
                        resolve(userInfo);

                    }).catch(function(error) {
                        $localStorage.clear();
                        reject(error.message);
                    });
                } else {
                    resolve();
                }
            });

        };

        service.handleOnSuccessLogin = function(invitationId) {
            return new Promise(function(resolve, reject) {
                service.setProfile().then(function(user) {
                    if (invitationId !== undefined) {
                        Invitation.get(invitationId).then(function(res) {
                            if (!res.data.user) {
                                var invitToUpdate = {
                                    id: invitationId,
                                    is_pending: false,
                                    user: {
                                        id: user.id,
                                    },
                                };
                                Invitation.update(invitationId, invitToUpdate);
                            }
                            resolve();
                        });
                    }
                    else {
                        resolve();
                    }
                }).catch(function(error) {
                    reject({error: error.message});
                });

            });
        };
    }]);