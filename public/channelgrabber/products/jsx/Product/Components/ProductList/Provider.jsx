import React from 'react';
import {applyMiddleware, createStore} from 'redux';
import {Provider} from 'react-redux';
import thunk from 'redux-thunk';
import getStateExtender from 'Product/Components/ProductList/getStateExtender';
import ActionCreators from 'Product/Components/ProductList/ActionCreators/productActions';
import columnActions from 'Product/Components/ProductList/ActionCreators/columnActions';
import combinedReducer from 'Product/Components/ProductList/Reducers/combinedReducer';
import ProductListRoot from 'Product/Components/ProductList/Root';

var enhancer = applyMiddleware(thunk);

if (window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
    enhancer = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
        latency: 0,
        name: 'ProductsList'
    })(applyMiddleware(
        thunk
    ));
}
var store = createStore(
    combinedReducer,
    enhancer
);
store.getState = getStateExtender(store.getState);

class ProductListProvider extends React.Component {
    static defaultProps = {
        products: [],
        features: {},
        allProductsLinks: {}
    };

    state = {
        initialProductsSaved: {}
    };

    async componentDidMount() {
        store.dispatch(ActionCreators.storeAccountFeatures(this.props.features));
        store.dispatch(ActionCreators.storeStockModeOptions(this.props.stockModeOptions));
        await store.dispatch(ActionCreators.getProducts());
        store.dispatch(columnActions.generateColumnSettings());
    }

    render() {
        return (
            <Provider store={store}>
                <ProductListRoot {...this.props} />
            </Provider>
        );
    }
}

export default ProductListProvider;