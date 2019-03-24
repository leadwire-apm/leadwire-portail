(function(angular) {
    angular.module('leadwireApp').service('InvitationService', [
        'InvitationFactory',
        function(InvitationFactory) {
            var service = this;
            service.acceptInvitation = function(invitationId, userId) {
                return new Promise(function(resolve, reject) {
                    InvitationFactory.get(invitationId)
                        .then(function(response) {
                            if (response.data.pending == true) {
                                InvitationFactory.accept(invitationId, {'userId': userId});
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
