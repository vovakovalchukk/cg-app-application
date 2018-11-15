import React from 'react';
import {applyMiddleware, createStore} from 'redux';
import {Provider} from 'react-redux';
import thunk from 'redux-thunk';
import getStateExtender from 'Product/Components/ProductList/getStateExtender';
import productActions from 'Product/Components/ProductList/ActionCreators/productActions';
import columnActions from 'Product/Components/ProductList/ActionCreators/columnActions';
import userSettingsActions from 'Product/Components/ProductList/ActionCreators/userSettingsActions';
import combinedReducer from 'Product/Components/ProductList/Reducers/combinedReducer';
import ProductListRoot from 'Product/Components/ProductList/Root';
import stateUtility from 'Product/Components/ProductList/stateUtility';

var enhancer = applyMiddleware(thunk);

if (typeof window === 'object' &&
    window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
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
        const {massUnit, lengthUnit} = this.props;
        store.dispatch(productActions.storeAccountFeatures(this.props.features));
        store.dispatch(productActions.storeStockModeOptions(this.props.stockModeOptions));
        store.dispatch(userSettingsActions.storeMetrics({massUnit,lengthUnit}));
        let productsResp = await store.dispatch(productActions.getProducts());
        store.dispatch(columnActions.generateColumnSettings());
        store.dispatch(userSettingsActions.storeStockDefaults(
            stateUtility.getDefaultStockModeFromProducts(productsResp.products),
            stateUtility.getDefaultStockLevelFromProducts(productsResp.products),
        ));
    }

    render() {
        return (
            <Provider
                store={store}
            >
                <ProductListRoot
                    {...this.props}
                />
            </Provider>
        );
    }
}

export default ProductListProvider;