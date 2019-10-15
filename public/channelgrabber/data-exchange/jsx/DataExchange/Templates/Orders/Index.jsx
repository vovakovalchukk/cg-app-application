import React from 'react';
import ReactDOM from 'react-dom';
import TemplateFieldMapper from '../TemplateFieldMapper.jsx';

const OrdersTemplates = function(
    mountingNode,
    templates,
    cgFieldOptions
) {
    ReactDOM.render(
        <TemplateFieldMapper
            templates={templates}
            cgFieldOptions={cgFieldOptions}
        />,
        mountingNode
    );
};

export default OrdersTemplates;