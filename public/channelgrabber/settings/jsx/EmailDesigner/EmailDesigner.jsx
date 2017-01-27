define([
    'React',
    'react-dom',
    'EmailDesigner/Components/Root'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var EmailDesigner = function(mountingNodes) {
        ReactDOM.render(<RootComponent />, mountingNodes.emailDesignerRoot);
    };
    return EmailDesigner;
});