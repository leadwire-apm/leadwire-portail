(function(angular) {
    angular
        .module('leadwireApp')
        .service('UserService', [
            'Account',
            '$rootScope',
            '$localStorage',
            'Invitation',
            'FileService',
            UserServiceFN
        ]);

    function UserServiceFN(
        Account,
        $rootScope,
        $localStorage,
        Invitation,
        FileService
    ) {
        var service = this;
        var sep = '###';

        service.setProfile = function() {
            return new Promise(function(resolve, reject) {
                if (
                    angular.isUndefined($localStorage.user) ||
                    $localStorage.user === null
                ) {
                    Account.getProfile()
                        .then(function(response) {
                            var userInfo = response.data;
                            var sep = '###';
                            var contactInfos = response.data.contact
                                ? response.data.contact.split(sep)
                                : [];
                            if (contactInfos.length) {
                                userInfo.phoneCode = contactInfos[0];
                                userInfo.contact = contactInfos[1];
                            } else {
                                userInfo.phoneCode = null;
                                userInfo.contact = null;
                            }
                            userInfo.fname = response.data.login;
                            if (
                                angular.isDefined(response.data.displayName) &&
                                response.data.displayName !== null
                            ) {
                                userInfo.fname = response.data.displayName;
                            }

                            userInfo.avatar = response.data.avatar;
                            $localStorage.user = userInfo;
                            $rootScope.$broadcast('user:updated', userInfo);
                            resolve($localStorage.user);
                        })
                        .catch(function(error) {
                            $localStorage.$reset();
                            console.log(error);
                            reject(error);
                        });
                } else {
                    resolve($localStorage.user);
                }
            });
        };

        service.saveUser = function(user, avatar) {
            return new Promise(function(resolve, reject) {
                var updatedInfo = service.transformUser(user);
                Account.updateProfile(updatedInfo)
                    .then(function(data) {
                        $localStorage.user = angular.extend(
                            $localStorage.user,
                            updatedInfo,
                            {
                                contact: user.contact,
                                phoneCode: user.phoneCode
                            }
                        );

                        if (data) {
                            //if he updated his avatar we need to make another request
                            if (avatar) {
                                FileService.upload(avatar, 'user').then(
                                    function(response) {
                                        Account.updateProfile({
                                            id: user.id,
                                            avatar: response.data.name
                                        });
                                        $localStorage.user = angular.extend(
                                            $localStorage.user,
                                            {
                                                avatar: response.data.name
                                            }
                                        );
                                        resolve(response.data.name);
                                    }
                                );
                            } else {
                                resolve();
                            }
                        } else {
                            throw new Error(data);
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                        reject('Failed update User');
                    });
            });
        };

        service.handleBeforeRedirect = function(invitationId) {
            return new Promise(function(resolve, reject) {
                service
                    .setProfile()
                    .then(function(user) {
                        if (invitationId !== undefined) {
                            Invitation.get(invitationId).then(function(res) {
                                if (!res.data.user) {
                                    var invitToUpdate = {
                                        id: invitationId,
                                        isPending: false,
                                        user: {
                                            id: user.id
                                        },
                                        app: {
                                            id: res.data.app.id
                                        }
                                    };
                                    Invitation.update(
                                        invitationId,
                                        invitToUpdate
                                    );
                                }
                                resolve($localStorage.user);
                            });
                        } else {
                            resolve($localStorage.user);
                        }
                    })
                    .catch(function(error) {
                        reject(error);
                    });
            });
        };

        service.transformUser = function(user) {
            var phone = user.contact
                ? user.phoneCode + sep + user.contact
                : null;
            var updatedInfo = {
                id: user.id,
                email: user.email,
                company: user.company,
                acceptNewsLetter: user.acceptNewsLetter,
                contact: phone,
                contactPreference: user.contactPreference,
                defaultApp:
                    user.defaultApp && user.defaultApp.id
                        ? { id: user.defaultApp.id }
                        : null,

                username: user.username,
                name: user.name
            };
            return updatedInfo;
        };
    }
})(window.angular);