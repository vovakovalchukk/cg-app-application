define([
    'react',
    'redux',
    'react-redux',
    'redux-thunk',
    'Product/Components/CreateProduct/CreateProductActionCreators',
    'Product/Components/CreateProduct/Reducers/CombinedReducer',
    'Product/Components/CreateProduct/CreateProduct'
], function(
    React,
    Redux,
    ReactRedux,
    thunk,
    ActionCreators,
    CombinedReducer,
    CreateProduct
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

    var CreateProductRoot = React.createClass({
        getDefaultProps: function() {
            return {
                onCreateProductClose: null,
                stockModeOptions: null,
                onSaveAndList: null
            };
        },
        formSubmit: function(values) {
            store.dispatch(ActionCreators.formSubmit(values, this.props.redirectToProducts));
        },
        formContainerSubmitClick: function() {
            store.dispatch(ActionCreators.formContainerSubmitClick());
        },
        resetCreateProducts: function() {
            store.dispatch(ActionCreators.userLeavesCreateProduct());
        },
        componentWillMount: function() {
            store.dispatch(ActionCreators.initialAccountDataLoaded(this.props.taxRates, this.props.stockModeOptions))
        },
        render: function() {
            return (
                <Provider store={store}>
                    <CreateProduct
                        onCreateProductClose={this.props.onCreateProductClose}
                        resetCreateProducts={this.resetCreateProducts}
                        formSubmit={this.formSubmit}
                        formContainerSubmitClick={this.formContainerSubmitClick}
                        redirectToProducts={this.props.redirectToProducts}
                        onSaveAndList={this.props.onSaveAndList}
                    />
                </Provider>
            );
        }
    });

    return CreateProductRoot;
});
