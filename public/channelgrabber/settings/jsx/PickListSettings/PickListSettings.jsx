import React from 'react';
import ReactDOM from 'react-dom';
import RootComponent from './Components/Root';

var PickListSettings = function(domNode, eTag, pickList, sortFields, sortFieldsMap, sortDirections, saveUrl) {
    ReactDOM.render(
        <RootComponent
            eTag={eTag}
            pickList={pickList}
            sortFields={sortFields}
            sortFieldsMap={sortFieldsMap}
            sortDirections={sortDirections}
            saveUrl={saveUrl}
        />,
        domNode
    );
};

export default PickListSettings;