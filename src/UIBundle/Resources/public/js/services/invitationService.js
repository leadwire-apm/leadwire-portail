(function(angular) {
    angular.module('leadwireApp').service('InvitationService', [
        'Invitation',
        function(InvitationFactory) {
            var service = this;
            service.acceptInvitation = function(invitationId, userId) {
                return new Promise(function(resolve, reject) {
                    InvitationFactory.get(invitationId)
                        .then(function(response) {
                            if (!response.data.user && response.data.app) {
                                InvitationFactory.update(invitationId, {
                                    id: invitationId,
                                    isPending: false,
                                    user: {
                                        id: userId
                                    },
                                    application: {
                                        id: response.data.app.id
                                    }
                                });
                            }
                            resolve(response.data.app);
                        })
                        .catch(function(error) {
                            reject();
                        });
                });
            };
        }
    ]);
})(window.angular);
