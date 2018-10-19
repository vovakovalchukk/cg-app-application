import React from 'react';
import Redux from 'redux';
import ReactDom from 'react-dom';
import ReactRedux from 'react-redux';
import ReduxForm from 'redux-form';
import thunk from 'redux-thunk';
import Container from 'Common/Components/Container';
import CombinedReducer from 'Product/Components/CreateListing/Reducers/CreateListing/Combined';
import CreateListingPopup from 'Product/Components/CreateListing/CreateListingPopup';

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

class CreateListingRoot extends React.Component {
    static defaultProps = {
        product: {},
        accounts: [],
        categories: [],
        conditionOptions: [],
        variationsDataForProduct: {},
        accountsData: {},
        defaultCurrency: null,
        massUnit: null,
        lengthUnit: null
    };

    render() {
        return (
            <Provider store={store}>
                <CreateListingPopup
                    {...this.props}
                />
            </Provider>
        );
    }
}

export default CreateListingRoot;

