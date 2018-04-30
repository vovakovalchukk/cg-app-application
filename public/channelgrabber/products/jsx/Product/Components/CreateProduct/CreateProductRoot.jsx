define([
    'react',
    'redux',
    'react-redux',
    'redux-thunk',
    'Product/Components/CreateProduct/Reducers/CombinedReducer',
    'Product/Components/CreateProduct/CreateProduct'
], function(
    React,
    Redux,
    ReactRedux,
    thunk,
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
        componentWillMount: function() {
            store.dispatch({
                type: 'INITIAL_ACCOUNT_DATA_LOADED',
                payload: {
                    taxRates: this.props.taxRates,
                    stockModeOptions: this.props.stockModeOptions
                }
            });
        },
        render: function() {
            return (
                <Provider store={store}>
                    <CreateProduct
                        onCreateProductClose={this.props.onCreateProductClose}
                    />
                </Provider>
            );
        }
    });

    return CreateProductRoot;
});
