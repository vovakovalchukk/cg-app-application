define([
    'react',
    'react-dom',
    'ManualOrder/Components/Root',
    'Common/Components/Notes/Root'
], function(
    React,
    ReactDOM,
    RootComponent,
    NoteComponent
) {
    var ManualOrder = function(mountingNodes, utilities, currentUser)
    {
        ReactDOM.render(<RootComponent utilities={utilities}/>, mountingNodes.productInfo);
        ReactDOM.render(<NoteComponent author={currentUser}/>, mountingNodes.orderNotes);
    };

    return ManualOrder;
});