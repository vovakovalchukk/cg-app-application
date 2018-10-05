import React from 'react';
import {applyMiddleware, createStore} from 'redux';
import {Provider} from 'react-redux';
import thunk from 'redux-thunk';
import CombinedReducer from './Reducers/Combined';
import ProductSearchComponent from './Component';

let enhancer = applyMiddleware(thunk);
if (window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
    enhancer = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
        latency: 0
    })(applyMiddleware(thunk));
}
const store = createStore(
    CombinedReducer,
    enhancer
);

class ProductSearchRoot extends React.Component {
    static defaultProps = {
        createListingData: {},
        renderCreateListingPopup: () => {}
    };

    render() {
        return (
            <Provider store={store}>
                <ProductSearchComponent
                    {...this.props}
                    accountId={this.props.createListingData.searchAccountId}
                />
            </Provider>
        );
    }
}

export default ProductSearchRoot;

