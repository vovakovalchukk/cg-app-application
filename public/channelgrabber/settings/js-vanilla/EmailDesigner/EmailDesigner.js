define(['react', 'react-dom', 'EmailDesigner/Components/Root'], function (React, ReactDOM, RootComponent) {
    var EmailDesigner = function (mountingNodes) {
        ReactDOM.render(React.createElement(RootComponent, null), mountingNodes.emailDesignerRoot);
    };
    return EmailDesigner;
});
