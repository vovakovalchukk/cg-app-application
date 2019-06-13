import React, {useState} from 'react';
import ReactDOM from 'react-dom';
import Root from 'Courier/Review/Components/Root';

export default function createCourierReviewRoot({
        CourierReviewService,
        orderIds,
        mountNode,
        courierAjaxRoute,
        servicesAjaxRoute
    }) {
    ReactDOM.render(
        <Root
            courierAjaxRoute={courierAjaxRoute}
            servicesAjaxRoute={servicesAjaxRoute}
        />,
        mountNode
    );
};