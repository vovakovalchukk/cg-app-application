import Redux from 'redux';
import ReduxForm from 'redux-form';
import ProductsSearchReducer from './Products';
    

    const CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        products: ProductsSearchReducer
    });

    export default CombinedReducer;

