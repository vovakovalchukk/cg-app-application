import React from 'react';
import ReactDOM from 'react-dom';
import TemplateFieldMapper from 'DataExchange/Templates/TemplateFieldMapper.jsx';

const OrderTrackingTemplates = function(
    mountingNode,
    templates,
    cgFieldOptions
) {
    ReactDOM.render(
        <TemplateFieldMapper
            templates={templates}
            cgFieldOptions={cgFieldOptions}
            templateType={'orderTracking'}
            xhrRoute={'orderTracking'}
        />,
        mountingNode
    );
};

export default OrderTrackingTemplates;