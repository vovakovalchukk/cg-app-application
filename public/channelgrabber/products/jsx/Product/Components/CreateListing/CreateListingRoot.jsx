import React from 'react';
import {applyMiddleware, createStore} from 'redux';
import {Provider} from 'react-redux';
import thunk from 'redux-thunk';
import CombinedReducer from 'Product/Components/CreateListing/Reducers/CreateListing/Combined';
import CreateListingPopup from 'Product/Components/CreateListing/CreateListingPopup';

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
        lengthUnit: null,
        categoryTemplateOptions: {}
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

