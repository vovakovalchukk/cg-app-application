define([
    'react',
    'react-dom',
    'redux',
    'react-redux',
    'Redux/Components/Root',
    'Redux/Reducers/Combined'
], function(
    React,
    ReactDOM,
    Redux,
    ReactRedux,
    RootComponent,
    Reducer
) {
    var Prototype = function(
        mountingNode
    ) {
        var store = Redux.createStore(Reducer);
        var Provider = ReactRedux.Provider;
        ReactDOM.render(
            <Provider store={store}>
                <RootComponent />
            </Provider>,
            mountingNode
        );
    };

    return Prototype;
});