import React from 'react';
import {applyMiddleware, createStore} from 'redux';
import {Provider} from 'react-redux';
import thunk from 'redux-thunk';
import ActionCreators from 'Product/Components/CreateProduct/CreateProductActionCreators';
import CombinedReducer from 'Product/Components/CreateProduct/Reducers/CombinedReducer';
import CreateProduct from 'Product/Components/CreateProduct/CreateProduct';

    var enhancer = applyMiddleware(thunk);
    if (window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
        enhancer = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
            latency: 0
        })(applyMiddleware(thunk));
    }
    var store = createStore(
        CombinedReducer,
        enhancer
    );

    var CreateProductRoot = React.createClass({
        getDefaultProps: function() {
            return {
                onCreateProductClose: null,
                stockModeOptions: null,
                onSaveAndList: null,
                showVAT: true,
                massUnit: null,
                lengthUnit: null
            };
        },
        formSubmit: function(values) {
            store.dispatch(ActionCreators.formSubmit(values, this.props.redirectToProducts));
        },
        formContainerSubmitClick: function() {
            store.dispatch(ActionCreators.formContainerSubmitClick());
        },
        resetCreateProducts: function() {
            store.dispatch(ActionCreators.userLeavesCreateProduct());
        },
        componentWillMount: function() {
            store.dispatch(ActionCreators.initialAccountDataLoaded(this.props.taxRates, this.props.stockModeOptions))
        },
        render: function() {
            return (
                <Provider store={store}>
                    <CreateProduct
                        onCreateProductClose={this.props.onCreateProductClose}
                        resetCreateProducts={this.resetCreateProducts}
                        formSubmit={this.formSubmit}
                        formContainerSubmitClick={this.formContainerSubmitClick}
                        redirectToProducts={this.props.redirectToProducts}
                        onSaveAndList={this.props.onSaveAndList}
                        showVAT={this.props.showVAT}
                        massUnit={this.props.massUnit}
                        lengthUnit={this.props.lengthUnit}
                    />
                </Provider>
            );
        }
    });

    export default CreateProductRoot;

