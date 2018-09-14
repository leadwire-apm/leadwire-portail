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

        /**
         * refresh user stored in localStorage
         *
         * @param force
         * @returns {Promise}
         */
        service.setProfile = function(force) {
            return new Promise(function(resolve, reject) {
                if (
                    angular.isUndefined($localStorage.user) ||
                    $localStorage.user === null ||
                    force
                ) {
                    Account.getProfile()
                        .then(function(response) {
                            var userInfo = response.data;
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

        /**
         * Update user infos and upload new image if needed
         *
         * @param user
         * @param avatar
         * @returns {Promise}
         */
        service.saveUser = function(user, avatar) {
            return new Promise(function(resolve, reject) {
                var updatedInfo = service.transformUser(user);
                Account.updateProfile(updatedInfo)
                    .then(function(updated) {
                        $localStorage.user = angular.extend(
                            $localStorage.user,
                            updatedInfo,
                            {
                                contact: user.contact,
                                phoneCode: user.phoneCode
                            }
                        );

                        if (updated) {
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
                            throw new Error(updated);
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                        reject('Failed update User');
                    });
            });
        };

        /**
         * On login we updated user in localStorage and
         * we need to check if there is an invitation in the url
         * in this case we accept it if its not accepted yet
         *
         * @param invitationId
         * @returns {Promise}
         */
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
                                        'handleBeforeRedirect 1',
                                        error
                                    );
                                    resolve($localStorage.user);
                                });
                        } else {
                            resolve($localStorage.user);
                        }
                    })
                    .catch(function(error) {
                        console.log('handleBeforeRedirect 2', error);
                        reject(error);
                    });
            });
        };

        /**
         * change object shape to match Backend needs
         *
         * @param user
         * @returns {Object}
         */
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

        /**
         * Subscribe to a plan
         *
         * @param billingInfo
         * @returns {Promise}
         */
        service.subscribe = function(billingInfo) {
            var payload = angular.copy(billingInfo);
            if (payload.card && payload.card.expiry) {
                var expiryInfos = payload.card.expiry.split('/');
                payload.card.expiryMonth = expiryInfos[0].trim();
                payload.card.expiryYear = expiryInfos[1].trim();
                delete payload.card.expiry;
            } else {
                delete payload.card;
            }

            return Account.subscribe(payload, $localStorage.user.id);
        };

        /**
         * On first login we need to show a model
         * to update user information
         * and subscribe to a plan
         */
        service.handleFirstLogin = function() {
            var connectedUser = angular.extend({}, $localStorage.user);
            // var connectedUser = {id:'sa'};
            if (
                connectedUser.id &&
                (!connectedUser.email || !connectedUser.plan)
            ) {
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
                        // show modal
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

        /**
         * Load invoices
         *
         * @returns {*}
         */
        service.getInvoices = function() {
            return Account.invoices($localStorage.user.id);
        };
        /**
         * load subscription
         *
         * @returns {*}
         */
        service.getSubscription = function() {
            return Account.subscription($localStorage.user.id);
        };

        /**
         * change plan
         *
         * @param body
         * @returns {*}
         */
        service.updateSubscription = function(body) {
            return Account.updateSubscription(body, $localStorage.user.id).then(
                function(updateResponse) {
                    if (updateResponse.status === 200) {
                        return service.setProfile(true).then(function() {
                            return updateResponse;
                        });
                    } else {
                        return updateResponse;
                    }
                }
            );
        };

        /**
         * edit payment information
         *
         * @param cardInfo
         * @returns {*}
         */
        service.updatePaymentMethod = function(cardInfo) {
            var payload = angular.copy(cardInfo);
            var expiryInfos = payload.expiry.split('/');
            payload.expiryMonth = expiryInfos[0].trim();
            payload.expiryYear = expiryInfos[1].trim();
            delete payload.expiry;

            return Account.editPaymentMethod(payload, $localStorage.user.id);
        };
    }
})(window.angular);
