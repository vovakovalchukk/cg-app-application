import {combineReducers} from 'redux';
import {reducer as reduxFormReducer} from 'redux-form';
import ProductsSearchReducer from './Products';
    

    const CombinedReducer = combineReducers({
        form: reduxFormReducer,
        products: ProductsSearchReducer
    });

    export default CombinedReducer;

