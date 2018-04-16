define([
    'react',
    'redux',
    'react-redux',
    'redux-thunk',
    'Product/Components/CreateProduct/Reducers/CombinedReducer',
    'Product/Components/CreateProduct/CreateProduct'
], function (
    React,
    Redux,
    ReactRedux,
    thunk,
    CombinedReducer,
    CreateProduct
) {
    "use strict";
    
    var Provider = ReactRedux.Provider;


    var store = Redux.createStore(
        CombinedReducer,
        Redux.applyMiddleware(thunk.default)
    );
    
    var CreateProductRoot = React.createClass({
        getDefaultProps: function () {
            return {
                onCreateProductClose: null
            };
        },
        render: function () {
            return (
                <Provider store={store}>
                    <CreateProduct onCreateProductClose={this.props.onCreateProductClose}/>
                </Provider>
            );
        }
    });
    
    return CreateProductRoot;
});
