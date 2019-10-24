import React from 'react';
import ReactDOM from 'react-dom';
import TemplateFieldMapper from 'DataExchange/Templates/TemplateFieldMapper.jsx';

const OrdersTemplates = function(
    mountingNode,
    templates,
    cgFieldOptions
) {
    ReactDOM.render(
        <TemplateFieldMapper
            templates={templates}
            cgFieldOptions={cgFieldOptions}
            templateType={'order'}
            xhrRoute={'orders'}
        />,
        mountingNode
    );
};

export default OrdersTemplates;