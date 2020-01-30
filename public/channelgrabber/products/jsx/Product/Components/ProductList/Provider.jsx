import React from 'react';
import {applyMiddleware, createStore} from 'redux';
import {Provider} from 'react-redux';
import thunk from 'redux-thunk';
import getStateExtender from 'Product/Components/ProductList/getStateExtender';
import productActions from 'Product/Components/ProductList/ActionCreators/productActions';
import columnActions from 'Product/Components/ProductList/ActionCreators/columnActions';
import vatActions from 'Product/Components/ProductList/ActionCreators/vatActions';
import userSettingsActions from 'Product/Components/ProductList/ActionCreators/userSettingsActions';
import combinedReducer from 'Product/Components/ProductList/Reducers/combinedReducer';
import ProductListRoot from 'Product/Components/ProductList/Root';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import tabActions from 'Product/Components/ProductList/ActionCreators/tabActions';
import pickLocationsActions from 'Product/Components/ProductList/ActionCreators/pickLocationsActions';
import expandActions from "./ActionCreators/expandActions";
import supplierActions from "./ActionCreators/supplierActions";

var enhancer = applyMiddleware(thunk);

if (typeof window === 'object' &&
    window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
    enhancer = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
        latency: 0,
        name: 'ProductsList',
        trace: true
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
        const {massUnit, lengthUnit, vatRates} = this.props;
        store.dispatch(productActions.storeAccountFeatures(this.props.features));
        store.dispatch(productActions.storeStockModeOptions(this.props.stockModeOptions));
        store.dispatch(productActions.storeIncPOStockInAvailableOptions(this.props.incPOStockInAvailableOptions));
        store.dispatch(userSettingsActions.storeMetrics({massUnit, lengthUnit}));
        store.dispatch(vatActions.storeVatRates(vatRates));
        store.dispatch(pickLocationsActions.storePickLocationNames(this.props.pickLocations));
        store.dispatch(pickLocationsActions.storePickLocationValues(this.props.pickLocationValues));

        if (this.props.features.stockTabEnabled) {
            store.dispatch(tabActions.showStockTab());
        }

        store.dispatch(expandActions.changeStatusExpandAll('collapsed'));

        let productsResponse = await store.dispatch(productActions.getProducts());

        store.dispatch(columnActions.generateColumnSettings(this.props.features));

        store.dispatch(userSettingsActions.storeStockDefaults(
            stateUtility.getDefaultStockModeFromProducts(productsResponse.products),
            stateUtility.getDefaultStockLevelFromProducts(productsResponse.products)
        ));
        store.dispatch(userSettingsActions.storeLowStockThresholdDefaults(
            stateUtility.getLowStockThresholdDefaultsFromProducts(productsResponse.products)
        ));
        store.dispatch(userSettingsActions.storeDefaultReorderQuantity(
            stateUtility.getDefaultReorderQuantityFromProducts(productsResponse.products)
        ));
        store.dispatch(supplierActions.storeOptions(this.props.supplierOptions));
        store.dispatch(supplierActions.extractSuppliers(productsResponse.products));
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