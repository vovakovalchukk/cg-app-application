import React from 'react';
import Redux from 'redux';
import ReactRedux from 'react-redux';
import thunk from 'redux-thunk';
import ActionCreators from 'Product/Components/CreateProduct/CreateProductActionCreators';
import CombinedReducer from 'Product/Components/CreateProduct/Reducers/CombinedReducer';
import CreateProduct from 'Product/Components/CreateProduct/CreateProduct';

var Provider = ReactRedux.Provider;

var enhancer = Redux.applyMiddleware(thunk.default);
if (window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
    enhancer = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
        latency: 0
    })(Redux.applyMiddleware(thunk.default));
}
var store = Redux.createStore(
    CombinedReducer,
    enhancer
);

class CreateProductRoot extends React.Component {
    static defaultProps = {
        onCreateProductClose: null,
        stockModeOptions: null,
        onSaveAndList: null,
        showVAT: true,
        massUnit: null,
        lengthUnit: null
    };

    formSubmit = (values) => {
        store.dispatch(ActionCreators.formSubmit(values, this.props.redirectToProducts));
    };

    formContainerSubmitClick = () => {
        store.dispatch(ActionCreators.formContainerSubmitClick());
    };

    resetCreateProducts = () => {
        store.dispatch(ActionCreators.userLeavesCreateProduct());
    };

    componentWillMount() {
        store.dispatch(ActionCreators.initialAccountDataLoaded(this.props.taxRates, this.props.stockModeOptions))
    }

    render() {
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
}

export default CreateProductRoot;

