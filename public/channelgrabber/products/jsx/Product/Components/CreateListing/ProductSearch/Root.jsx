import React from 'react';
import Redux from 'redux';
import ReactRedux from 'react-redux';
import thunk from 'redux-thunk';
import CombinedReducer from './Reducers/Combined';
import ProductSearchComponent from './Component';
    

    const Provider = ReactRedux.Provider;

    let enhancer = Redux.applyMiddleware(thunk.default);
    if (window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
        enhancer = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
            latency: 0
        })(Redux.applyMiddleware(thunk.default));
    }
    const store = Redux.createStore(
        CombinedReducer,
        enhancer
    );

    const ProductSearchRoot = React.createClass({
        getDefaultProps: function() {
            return {
                createListingData: {},
                renderCreateListingPopup: () => {}
            }
        },
        render: function() {
            return (
                <Provider store={store}>
                    <ProductSearchComponent
                        {...this.props}
                        accountId={this.props.createListingData.searchAccountId}
                    />
                </Provider>
            );
        }
    });

    export default ProductSearchRoot;

