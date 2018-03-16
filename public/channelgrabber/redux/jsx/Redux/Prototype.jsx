define([
    'react',
    'react-dom',
    'redux',
    'react-redux',
    'react-router-dom',
    'Redux/Components/Root',
    'Redux/Reducers/Combined'
], function(
    React,
    ReactDOM,
    Redux,
    ReactRedux,
    ReactRouterDom,
    RootComponent,
    Reducer
) {
    var Prototype = function(
        mountingNode
    ) {
        var store = Redux.createStore(Reducer);
        var Provider = ReactRedux.Provider;
        var BrowserRouter = ReactRouterDom.BrowserRouter;

        ReactDOM.render(
            <Provider store={store}>
                <BrowserRouter>
                    <RootComponent />
                </BrowserRouter>
            </Provider>,
            mountingNode
        );
    };

    return Prototype;
});