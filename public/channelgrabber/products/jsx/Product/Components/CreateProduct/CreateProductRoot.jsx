define([
    'react',
    'redux',
    'react-redux',
    'Product/Components/CreateProduct/Reducers/CombinedReducer',
    'Product/Components/CreateProduct/CreateProduct'
], function (
    React,
    Redux,
    ReactRedux,
    CombinedReducer,
    CreateProduct
) {
    "use strict";
    
    var Provider = ReactRedux.Provider;
    
    var store = Redux.createStore(
        CombinedReducer, {}
    );
    
    var CreateProductRoot = React.createClass({
        getDefaultProps: function () {
            return {
                onCreateProductClose: null,
                onSaveAndList: null
            };
        },
        render: function () {
            return (
                <Provider store={store}>
                    <CreateProduct
                        onCreateProductClose={this.props.onCreateProductClose}
                        onSaveAndList={this.props.onSaveAndList}
                    />
                </Provider>
            );
        }
    });
    
    return CreateProductRoot;
});
