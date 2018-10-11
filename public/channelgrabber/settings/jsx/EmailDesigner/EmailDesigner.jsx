import React from 'react';
import ReactDOM from 'react-dom';
import RootComponent from 'EmailDesigner/Components/Root';
    var EmailDesigner = function(mountingNodes) {
        ReactDOM.render(<RootComponent />, mountingNodes.emailDesignerRoot);
    };
    export default EmailDesigner;
