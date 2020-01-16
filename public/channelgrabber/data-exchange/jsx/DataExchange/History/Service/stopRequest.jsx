import ajax from 'CommonSrc/js-vanilla/Common/Utils/xhr/ajax';

const stopRequest = (historyId, callback) => {
    ajax.request({
        method: 'POST',
        url: '/dataExchange/history/stop',
        data: {
            historyId
        },
        onError,
        onSuccess
    });

    function onError() {
        n.notice('There was a problem stopping this history. Please contact support for further information.')
    }

    function onSuccess(response) {
        const json = JSON.parse(response);
        if (!json.success) {
            onError();
            return;
        }
        callback();
    }
};

export default stopRequest;