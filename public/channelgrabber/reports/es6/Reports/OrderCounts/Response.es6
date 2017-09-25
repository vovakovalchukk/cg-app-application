define([], function() {
    class Response {
        static get allowed() {
            return {
                keys: ['total'],
                objectKeys: ['channel']
            };
        }
    }

    return Response;
});
