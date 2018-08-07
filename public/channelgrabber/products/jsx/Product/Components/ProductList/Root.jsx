define([
    'react',
    'redux',
    'react-redux',
    'redux-thunk',
    'Product/Components/ProductList/ActionCreators',
    'Product/Components/ProductList/Reducers/CombinedReducer',
    'Product/Components/ProductList/ProductList'
], function(
    React,
    Redux,
    ReactRedux,
    thunk,
    ActionCreators,
    CombinedReducer,
    ProductList
) {
    "use strict";
    
    let mapStateToProps = function(state) {
        return {
            products: state.products
        };
    };
    
    let mapDispatchToProps = function(dispatch, props) {
        return {
            // loadInitialValues: function() {
            //     dispatch(
            //         ActionCreators.loadInitialValues(
            //         )
            //     );
            // }
        };
    };
    
    
    // return ProductListRoot;
    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(ProductList);
});
