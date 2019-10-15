import React from 'react';
import ReactDOM from 'react-dom';
import TemplateFieldMapper from 'DataExchange/Templates/TemplateFieldMapper.jsx';

const StockTemplates = function(
    mountingNode,
    templates,
    cgFieldOptions
) {
    console.log('{mountingNode, templates, cgFieldOptions}: ', {mountingNode, templates, cgFieldOptions});
    
    ReactDOM.render(
        <TemplateFieldMapper
            templates={templates}
            cgFieldOptions={cgFieldOptions}
            templateType={'stock'}
        />,
        mountingNode
    );
};

export default StockTemplates;