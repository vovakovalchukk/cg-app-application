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
    'Product/Components/ProductList/ProductList'
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
    ProductList
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
    
    const mapDispatchToProps = function(dispatch,ownProps) {
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
    
    function formatPassedInMethodsAsReduxActions(ownProps){
        return {
            createNewListing: ({parentProductId}) => {
                return async function(dispatch, getState) {
                    console.log('in createNewListing successfully');
                    const state = getState();
                    
                    console.log('state: ', state);
                    console.log('ownProps: ', ownProps);
                    
                    if(parentProductId){
                        console.log('getting newVariations');
                        await this.props.actions.getVariationsByParentProductId(parentProductId);
                    }
                    //todo get params set on state
                    ownProps.onCreateNewListingIconClick({
                        //todo all the params should go here
                        accounts: state.accounts.getAccounts(state),
                        productSearchActive: state.search.productSearchActive
                        
                    })
                }
            }
        };
    }
});
