angular.module('leadwireApp').factory('Account', function($http, CONFIG) {
    return {
        /**
         *
         * @returns {Promise}
         */
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
                        var sep = '###';
                        var contactInfos = response.data.contact ?
                            response.data.contact.split(sep) :
                            [];
                        if (contactInfos.length) {
                            userInfo.phoneCode = contactInfos[0];
                            userInfo.contact = contactInfos[1];
                        } else {
                            userInfo.phoneCode = null;
                            userInfo.contact = null;

                        }
                        userInfo.fname = response.data.login;
                        if (angular.isDefined(response.data.displayName) &&
                            response.data.displayName !== null) {
                            userInfo.fname = response.data.displayName;
                        }

                        userInfo.avatar = response.data.avatar;
                        $localStorage.user = userInfo;
                        $rootScope.$broadcast('user:updated', userInfo);
                        resolve($localStorage.user);

                    }).catch(function(error) {
                        $localStorage.$reset();
                        console.log(error)
                        reject(error);
                    });
                } else {
                    resolve($localStorage.user);
                }
            });

        };

        service.handleBeforeRedirect = function(invitationId) {
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
                            resolve($localStorage.user);
                        });
                    }
                    else {
                        resolve($localStorage.user);
                    }
                }).catch(function(error) {
                    reject(error);
                });

            });
        };
    }]);