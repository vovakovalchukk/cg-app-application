import productActions from 'Product/Components/ProductList/ActionCreators/productActions'
import productLinkActions from 'Product/Components/ProductList/ActionCreators/productLinkActions'
import paginationActions from 'Product/Components/ProductList/ActionCreators/paginationActions';
import searchActions from 'Product/Components/ProductList/ActionCreators/searchActions'
import sortActions from 'Product/Components/ProductList/ActionCreators/sortActions';
import vatActions from 'Product/Components/ProductList/ActionCreators/vatActions'
import tabActions from 'Product/Components/ProductList/ActionCreators/tabActions'
import stockActions from 'Product/Components/ProductList/ActionCreators/stockActions'
import bulkSelectActions from 'Product/Components/ProductList/ActionCreators/bulkSelectActions';
import rowActions from 'Product/Components/ProductList/ActionCreators/rowActions';
import globalActions from 'Product/Components/ProductList/ActionCreators/globalActions';
import userSettingsActions from 'Product/Components/ProductList/ActionCreators/userSettingsActions';
import scrollActions from 'Product/Components/ProductList/ActionCreators/scrollActions';
import detailActions from 'Product/Components/ProductList/ActionCreators/detailActions';
import pickLocationsActions from 'Product/Components/ProductList/ActionCreators/pickLocationsActions';
import expandActions from 'Product/Components/ProductList/ActionCreators/expandActions';
import nameActions from 'Product/Components/ProductList/ActionCreators/nameActions';
import focusActions from 'Product/Components/ProductList/ActionCreators/focusActions';
import selectActions from 'Product/Components/ProductList/ActionCreators/selectActions';
import supplierActions from "./supplierActions";

export default (ownProps) => {
    let passedInMethodsAsActions = formatPassedInMethodsAsReduxActions(ownProps);
    return Object.assign(
        globalActions,
        productActions,
        productLinkActions,
        paginationActions,
        searchActions,
        sortActions,
        tabActions,
        stockActions,
        vatActions,
        bulkSelectActions,
        passedInMethodsAsActions,
        rowActions,
        userSettingsActions,
        scrollActions,
        detailActions,
        pickLocationsActions,
        expandActions,
        nameActions,
        focusActions,
        selectActions,
        supplierActions
    );
}

function formatPassedInMethodsAsReduxActions(ownProps) {
    return {
        createNewListing: ({rowData}) => {
            return async function(dispatch, getState) {
                const state = getState();

                if (rowData.parentProductId) {
                    await productActions.getVariationsByParentProductId(rowData.parentProductId);
                }

                let idToGetProductFor = rowData.parentProductId === 0 ? rowData.id : rowData.parentProductId;
                let product = getState.customGetters.getProductById(idToGetProductFor);
                let variations = await getVariations(product, state, dispatch);

                ownProps.onCreateNewListingIconClick({
                    product,
                    variations,
                    accounts: state.accounts.getAccounts(state),
                    productSearchActive: state.search.productSearchActive,
                    createListingsAllowedChannels: state.createListing.createListingsAllowedChannels,
                    createListingsAllowedVariationChannels: state.createListing.createListingsAllowedVariationChannels,
                });

                dispatch(globalActions.changeView());
            }
        }
    };
}

async function getVariations(product, state, dispatch){
    if(product.variationCount === 0){
        return [product];
    }
    if(state.products.variationsByParent[product.id]){
        return state.products.variationsByParent[product.id];
    }
    return await getVariationsForProductThatHasNotBeenExpandedYet(dispatch, product);
}

async function getVariationsForProductThatHasNotBeenExpandedYet(dispatch, product) {
    $('#products-loading-message').show();
    let getVariationsResponse = await dispatch(productActions.getVariationsByParentProductId(product.id));
    $('#products-loading-message').hide();
    let newVariations = getVariationsResponse.products;
    return newVariations;
}