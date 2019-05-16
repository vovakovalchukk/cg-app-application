import React from 'react';
import ReactDOM from 'react-dom';
import RootComponent from 'Settings/jsx/Listing/ListingTemplates/Root.jsx';

let ListingTemplate = function(
       mountingNode,
       templates,
       listingTemplateTags
) {
    ReactDOM.render(
        <RootComponent
            templates={templates}
            listingTemplateTags={listingTemplateTags}
        />,
        mountingNode
    );
};

export default ListingTemplate;