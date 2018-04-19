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

    var composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
        latency: 0
    });
    var store = Redux.createStore(
        CombinedReducer,
        composeEnhancers(
            Redux.applyMiddleware(thunk.default)
        )
    );

    var CreateProductRoot = React.createClass({
        getDefaultProps: function() {
            return {
                onCreateProductClose: null
            };
        },
        componentDidMount: function() {
            store.dispatch({
                type: 'INITIAL_ACCOUNT_DATA_LOADED',
                payload: {
                    taxRates: this.props.taxRates
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
