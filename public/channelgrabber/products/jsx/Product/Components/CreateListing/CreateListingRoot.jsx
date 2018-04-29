define([
    'react',
    'redux',
    'react-dom',
    'react-redux',
    'redux-form',
    'Common/Components/Container',
    'Product/Components/CreateListing/Reducers/CreateListing/Combined',
    'Product/Components/CreateListing/CreateListingPopup',
], function(
    React,
    Redux,
    ReactDom,
    ReactRedux,
    ReduxForm,
    Container,
    CombinedReducer,
    CreateListingPopup
) {
    "use strict";

    var Provider = ReactRedux.Provider;

    var CreateListingRoot = React.createClass({
        getDefaultProps: function() {
            return {
                product: {},
                accounts: [],
                categories: [],
                conditionOptions: [],
                variationsDataForProduct: {},
                fetchVariations: function() {}
            }
        },
        render: function() {
            var store = Redux.createStore(CombinedReducer);
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
