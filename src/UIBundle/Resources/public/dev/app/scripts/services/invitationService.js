(function(angular) {
    angular.module('leadwireApp').service('InvitationService', [
        'Invitation',
        function(InvitationFactory) {
            var service = this;
            service.acceptInvitation = function(invitationId, userId) {
                return new Promise(function(resolve, reject) {
                    InvitationFactory.get(invitationId)
                        .then(function(response) {
                            console.log('Invitation', response);
                            if (!response.data.user && response.data.app) {
                                var invitToUpdate = {
                                    id: invitationId,
                                    isPending: false,
                                    user: {
                                        id: userId
                                    },
                                    app: {
                                        id: response.data.app.id
                                    }
                                };
                                InvitationFactory.update(
                                    invitationId,
                                    invitToUpdate
                                );
                            }
                            resolve();
                        })
                        .catch(function(error) {
                            console.log('service.acceptInvitation', error);
                            reject();
                        });
                });
            };
        }
    ]);
})(window.angular);
