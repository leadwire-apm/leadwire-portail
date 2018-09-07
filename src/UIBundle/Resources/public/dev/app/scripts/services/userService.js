(function(angular) {
    angular
        .module('leadwireApp')
        .service('UserService', [
            'Account',
            '$rootScope',
            '$localStorage',
            'InvitationService',
            '$ocLazyLoad',
            '$modal',
            'FileService',
            UserServiceFN
        ]);

    function UserServiceFN(
        Account,
        $rootScope,
        $localStorage,
        InvitationService,
        $ocLazyLoad,
        $modal,
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
                            InvitationService.acceptInvitation(
                                invitationId,
                                user.id
                            )
                                .then(function() {
                                    resolve($localStorage.user);
                                })
                                .catch(function(error) {
                                    console.log(
                                        'service.handleBeforeRedirect 1',
                                        error
                                    );
                                    resolve($localStorage.user);
                                });
                        } else {
                            resolve($localStorage.user);
                        }
                    })
                    .catch(function(error) {
                        console.log('service.handleBeforeRedirect 2', error);
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

        service.subscribe = function(billingInfo) {
            var payload = angular.copy(billingInfo);
            if(payload.card && payload.card.expiry){
                var expiryInfos = payload.card.expiry.split('/');
                payload.card.expiryMonth = expiryInfos[0].trim();
                payload.card.expiryYear = expiryInfos[1].trim();
                delete payload.card.expiry;
            }else{
                delete payload.card;
            }

            return Account.subscribe(payload, $localStorage.user.id);
        };

        service.handleFirstLogin = function() {
            var connectedUser = angular.extend({}, $localStorage.user);
            // var connectedUser = null;
            if (connectedUser.id && (!connectedUser.email || !connectedUser.plan)) {
                $ocLazyLoad
                    .load({
                        insertBefore: '#load_styles_before',
                        files: [
                            $rootScope.ASSETS_BASE_URL +
                                'vendor/chosen_v1.4.0/chosen.min.css',
                            $rootScope.ASSETS_BASE_URL +
                                'vendor/chosen_v1.4.0/chosen.jquery.min.js',
                            $rootScope.ASSETS_BASE_URL +
                                'vendor/card/lib/js/jquery.card.js',
                            $rootScope.ASSETS_BASE_URL +
                                'vendor/jquery-validation/dist/jquery.validate.min.js',
                            $rootScope.ASSETS_BASE_URL +
                                'vendor/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js'
                        ]
                    })
                    .then(function() {
                        $ocLazyLoad
                            .load({
                                name: 'sbAdminApp',
                                files: [
                                    $rootScope.ASSETS_BASE_URL +
                                        'scripts/controllers/profileModal.js'
                                ]
                            })
                            .then(function() {
                                $modal.open({
                                    ariaLabelledBy: 'User-form',
                                    size: 'lg',
                                    keyboard: false,
                                    backdrop: 'static',
                                    ariaDescribedBy: 'User-form',
                                    templateUrl:
                                        $rootScope.ASSETS_BASE_URL +
                                        'views/wizard.html',
                                    controller: 'profileModalCtrl',
                                    controllerAs: 'ctrl'
                                });
                            });
                    });
            }
        };

        service.getInvoices = function() {
            return Account.invoices($localStorage.user.id);
        };
        service.getSubscription = function() {
            return Account.subscription($localStorage.user.id);
        };
        service.updateSubscription = function(body) {
            return Account.updateSubscription(body, $localStorage.user.id);
        };
    }
})(window.angular);
