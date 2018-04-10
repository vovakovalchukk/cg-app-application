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
        CombinedReducer,
        {
            // accounts: parseAccountData(data),
            // categories: parseCategoryData(data)
        }
    );

    var CreateProductRoot = React.createClass({

        getDefaultProps: function () {
            return {
                onCreateProductClose:null
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
