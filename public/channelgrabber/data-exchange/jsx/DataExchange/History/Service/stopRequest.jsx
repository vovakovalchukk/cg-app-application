import ajax from 'CommonSrc/js-vanilla/Common/Utils/xhr/ajax';

const stopRequest = (historyId, callback) => {
    console.log('in history fetch');
    let response = ajax.request({
        method: 'POST',
        url: '/dataExchange/history/stop',
        data: {
            historyId
        },
        onError,
        onSuccess
    });

    console.log('response: ', response);
    function onError() {
        n.notice('There was a problem stopping this history. Please contact support for further information.')
    }

    function onSuccess(response) {
        let json = JSON.parse(response);

        //todo - uncomment this hack
//        if (!json.success) {
//            onError();
//            return;
//        }
        // todo - just set value to "Stopping"
        callback();
    }
};

export default stopRequest;