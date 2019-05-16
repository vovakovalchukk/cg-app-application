import React from 'react';
import ReactDOM from 'react-dom';
import RootComponent from 'Settings/jsx/Listing/ListingTemplates/Root.jsx';

let ListingTemplate = function({
       mountingNode
}) {
    ReactDOM.render(
        <RootComponent/>,
        mountingNode
    );
};

export default ListingTemplate;