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
    var store = null;
    if (window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
        var composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
            latency: 0
        });
        store = Redux.createStore(
            CombinedReducer,
            composeEnhancers(
                Redux.applyMiddleware(thunk.default)
            )
        );
    } else {
        store = Redux.createStore(
            CombinedReducer,
            Redux.applyMiddleware(thunk.default)
        );
    }

    var CreateProductRoot = React.createClass({
        getDefaultProps: function() {
            return {
                onCreateProductClose: null,
                stockModeOptions: null
            };
        },
        formSubmit: function(values) {
            store.dispatch(ActionCreators.formSubmit(values,this.props.redirectToProducts));
        },
        formContainerSubmitClick:function(){
            store.dispatch(ActionCreators.formContainerSubmitClick());
        },
        componentWillMount: function() {
            store.dispatch(ActionCreators.initialAccountDataLoaded(this.props.taxRates, this.props.stockModeOptions))
        },
        render: function() {
            return (
                <Provider store={store}>
                    <CreateProduct
                        onCreateProductClose={this.props.onCreateProductClose}
                        formSubmit={this.formSubmit}
                        formContainerSubmitClick={this.formContainerSubmitClick}
                        redirectToProducts={this.props.redirectToProducts}
                    />
                </Provider>
            );
        }
    });

    return CreateProductRoot;
});
