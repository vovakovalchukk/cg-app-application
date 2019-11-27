import React from 'react';
import ajax from 'CommonSrc/js-vanilla/Common/Utils/xhr/ajax';

export default (pagination, setData) => {
    console.log('in history fetch');
    let response = ajax.request({
        method: 'POST',
        url: '/dataExchange/history/fetch',
        data: {
            page: pagination,
            limit: 50
        },
        onError,
        onSuccess
    });
    
    console.log('response: ', response);
    function onError() {
        console.log('inonerror');
        n.notice('There was a problem retrieving your history data. Please contact support for further information.')
    }   
    
    function onSuccess(data) {
        setData(JSON.parse(data).histories);
    }
}