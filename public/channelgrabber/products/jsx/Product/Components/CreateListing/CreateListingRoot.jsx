define([
    'react',
    'redux',
    'react-dom',
    'react-redux',
    'redux-form',
    'redux-thunk',
    'Common/Components/Container',
    'Product/Components/CreateListing/Reducers/CreateListing/Combined',
    'Product/Components/CreateListing/CreateListingPopup'
], function(
    React,
    Redux,
    ReactDom,
    ReactRedux,
    ReduxForm,
    thunk,
    Container,
    CombinedReducer,
    CreateListingPopup
) {
    "use strict";
    var Provider = ReactRedux.Provider;

    var enhancer = Redux.applyMiddleware(thunk.default);
    if (window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
        enhancer = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
            latency: 0
        })(Redux.applyMiddleware(thunk.default));
    }
    var store = Redux.createStore(
        CombinedReducer,
        enhancer
    );

    var CreateListingRoot = React.createClass({
        getDefaultProps: function() {
            return {
                product: {},
                accounts: [],
                categories: [],
                conditionOptions: [],
                variationsDataForProduct: {},
                accountsData: {},
                defaultCurrency: null
            }
        },
        render: function() {
            return (
                <Provider store={store}>
                    <CreateListingPopup
                        {...this.props}
                    />
                </Provider>
            );
        }
    });

    return CreateListingRoot;
});
