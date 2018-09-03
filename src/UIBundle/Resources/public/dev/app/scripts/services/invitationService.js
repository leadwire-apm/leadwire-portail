(function(angular) {
    angular.module('leadwireApp').service('InvitationService', [
        'Invitation',
        function(InvitationFactory) {
            var service = this;
            service.acceptInvitation = function(invitationId, userId) {
                return new Promise(function(resolve, reject) {
                    InvitationFactory.get(invitationId)
                        .then(function(res) {
                            if (!res.data.user) {
                                var invitToUpdate = {
                                    id: invitationId,
                                    isPending: false,
                                    user: {
                                        id: userId
                                    },
                                    app: {
                                        id: res.data.app.id
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
                            console.log(error);
                            reject();
                        });
                });
            };
        }
    ]);
})(window.angular);
