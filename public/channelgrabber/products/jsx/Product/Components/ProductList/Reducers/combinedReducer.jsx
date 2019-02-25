import {combineReducers} from 'redux';
import productsReducer from 'Product/Components/ProductList/Reducers/productsReducer';
import tabsReducer from 'Product/Components/ProductList/Reducers/tabsReducer';
import columnsReducer from 'Product/Components/ProductList/Reducers/columnsReducer';
import listReducer from 'Product/Components/ProductList/Reducers/listReducer';
import accountsReducer from 'Product/Components/ProductList/Reducers/accountsReducer';
import paginationReducer from 'Product/Components/ProductList/Reducers/paginationReducer';
import searchReducer from 'Product/Components/ProductList/Reducers/searchReducer';
import createListingReducer from 'Product/Components/ProductList/Reducers/createListingReducer';
import stockReducer from 'Product/Components/ProductList/Reducers/stockReducer';
import vatReducer from 'Product/Components/ProductList/Reducers/vatReducer';
import bulkSelectReducer from 'Product/Components/ProductList/Reducers/bulkSelectReducer';
import rowsReducer from 'Product/Components/ProductList/Reducers/rowsReducer';
import userSettingsReducer from 'Product/Components/ProductList/Reducers/userSettingsReducer';
import scrollReducer from 'Product/Components/ProductList/Reducers/scrollReducer';
import detailReducer from 'Product/Components/ProductList/Reducers/detailReducer';
import pickLocationsReducer from 'Product/Components/ProductList/Reducers/pickLocationsReducer';
import nameReducer from 'Product/Components/ProductList/Reducers/nameReducer';

var appReducer = combineReducers({
    products: productsReducer,
    tabs: tabsReducer,
    columns: columnsReducer,
    list: listReducer,
    accounts: accountsReducer,
    pagination: paginationReducer,
    search: searchReducer,
    createListing: createListingReducer,
    stock: stockReducer,
    vat: vatReducer,
    bulkSelect: bulkSelectReducer,
    rows: rowsReducer,
    userSettings: userSettingsReducer,
    scroll: scrollReducer,
    detail: detailReducer,
    pickLocations: pickLocationsReducer,
    name: nameReducer
});

const combinedReducer = (state, action) => {
    return appReducer(state, action);
};

export default combinedReducer;