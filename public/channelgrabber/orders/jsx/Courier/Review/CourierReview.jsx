import React, {useState} from 'react';
import ReactDOM from 'react-dom';
import Root from 'Courier/Review/Components/Root';
////
export default function({
        CourierReviewService,
        orderIds,
        mountNode,
        continueButton,
        ajaxRoute
    }) {
//
//    console.log('in function call... for creating the thing', {
//        CourierReviewService,
//        orderIds,
//        mountNode,
//        continueButton,
//        ajaxRoute
//    });

//    document.on('ajaxComplete', function getDataFromReviewAjax(event, xhr, settings) {
//        if (typeof ajaxRoute !== "string" || settings.url.toLowerCase() !== ajaxRoute.toLocaleLowerCase()) {
//            return;
//        }
//
//        let records = xhr.responseJSON.Records;
//        console.log('records: ', records);
//        debugger;
//        if (!records) {
//            return;
//        }
//        let allPossibleCourierOptions = getAllPossibleCourierOptions(records);
//        console.log('allPossibleCourierOptions: ', allPossibleCourierOptions);
//    });

    //
    ReactDOM.render(
        <Root
            continueButton={continueButton}
        />,
        mountNode
    );
};