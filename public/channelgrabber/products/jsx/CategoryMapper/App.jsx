define([
    'react',
    'react-dom',
    'redux',
    'react-redux',
    'CategoryMapper/Components/Root',
    'CategoryMapper/Reducers/CategoryMap'
], function(
    React,
    ReactDOM,
    Redux,
    ReactRedux,
    RootContainer,
    CategoryMapReducer
) {
    "use strict"

    var CreateNewApp = function(mountingNode, data) {
        var Provider = ReactRedux.Provider;
        var store = Redux.createStore(CategoryMapReducer);
        ReactDOM.render(
            <Provider store={store}>
                <RootContainer
                    accounts={data.accounts}
                    categories={data.categories}
                />
            </Provider>,
            mountingNode
        );
    };

    return CreateNewApp;
});
