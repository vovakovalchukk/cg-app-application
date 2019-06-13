import React, {useState} from 'react';
import ReactDOM from 'react-dom';
import Root from 'Courier/Review/Components/Root';

export default function createCourierReviewRoot({
        CourierReviewService,
        orderIds,
        mountNode,
        continueButton,
        ajaxRoute
    }) {
    ReactDOM.render(
        <Root
            continueButton={continueButton}
            ajaxRoute={ajaxRoute}
        />,
        mountNode
    );
};