define([
    'react',
    'react-dom',
    'Redux/Components/Root'
], function(
    React,
    ReactDOM,
    RootComponent
) {
    var Prototype = function(
        mountingNode
    ) {
        ReactDOM.render(
            <RootComponent
            />,
            mountingNode
        );
    };

    return Prototype;
});