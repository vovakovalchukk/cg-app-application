define([
    'react',
    'redux',
    'react-redux',
    'redux-thunk',
    'Product/Components/ProductList/ActionCreators/productActions',
    'Product/Components/ProductList/ActionCreators/productLinkActions',
    'Product/Components/ProductList/ActionCreators/paginationActions',
    'Product/Components/ProductList/ActionCreators/searchActions',
    'Product/Components/ProductList/ActionCreators/tabActions',
    'Product/Components/ProductList/ProductList',
    'Product/Components/ProductList/stateUtility'
], function(
    React,
    Redux,
    ReactRedux,
    thunk,
    productActions,
    productLinkActions,
    paginationActions,
    searchActions,
    tabActions,
    ProductList,
    stateUtility
) {
    "use strict";
    
    const mapStateToProps = function(state) {
        return {
            products: state.products,
            tabs: state.tabs,
            list: state.list,
            pagination: state.pagination,
            accounts: state.accounts.getAccounts(state),
            columns: state.columns
        };
    };
    
    const mapDispatchToProps = function(dispatch, ownProps) {
        let combinedActionCreators = combineActionCreators(ownProps);
        return {
            actions: Redux.bindActionCreators(
                combinedActionCreators,
                dispatch
            )
        };
    };
    
    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(ProductList);
    
    function combineActionCreators(ownProps) {
        let passedInMethodsAsActions = formatPassedInMethodsAsReduxActions(ownProps);
        return Object.assign(
            productActions,
            productLinkActions,
            paginationActions,
            searchActions,
            tabActions,
            passedInMethodsAsActions
        );
    }
    
    function formatPassedInMethodsAsReduxActions(ownProps) {
        return {
            createNewListing: ({rowData}) => {
                return async function(dispatch, getState) {
                    const state = getState();
                    // console.log('in createNewListing AC state: ',state);
                    // need to check whether we are getting the listing properties from state.
                    if (rowData.parentProductId) {
                        await productActions.getVariationsByParentProductId(parentProductId);
                    }
                    
                    let idToGetProductFor = rowData.parentProductId === 0 ? rowData.id : rowData.parentProductId;
                    let product = getState.customGetters.getProductById(idToGetProductFor);
                    //todo get params set on state
                    
                    ownProps.onCreateNewListingIconClick({
                        product,
                        variationsByParent: state.products.variationsByParent,
                        accounts: state.accounts.getAccounts(state),
                        productSearchActive: state.search.productSearchActive,
                        createListingsAllowedChannels: state.createListing.createListingsAllowedChannels,
                        createListingsAllowedVariationChannels: state.createListing.createListingsAllowedVariationChannels,
                    })
                }
            }
        };
    }
});
