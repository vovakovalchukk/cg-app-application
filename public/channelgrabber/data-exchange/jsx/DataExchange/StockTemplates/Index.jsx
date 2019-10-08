import React from 'react';
import ReactDOM from 'react-dom';
import AppComponent from 'DataExchange/StockTemplates/App.jsx';

const StockTemplates = function(
    mountingNode,
    templates,
    cgFieldOptions
) {
    console.log('{mountingNode, templates, cgFieldOptions}: ', {mountingNode, templates, cgFieldOptions});
    
    ReactDOM.render(
        <AppComponent
            templates={templates}
            cgFieldOptions={cgFieldOptions}
        />,
        mountingNode
    );
};

export default StockTemplates;