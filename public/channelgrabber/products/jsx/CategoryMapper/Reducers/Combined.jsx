import Redux from 'redux';
import ReduxForm from 'redux-form';
import AccountsReducer from 'CategoryMapper/Reducers/Accounts';
import CategoryMapsReducer from 'CategoryMapper/Reducers/CategoryMaps';
import CategoriesReducer from 'CategoryMapper/Reducers/Categories';
import InitialValuesReducer from 'CategoryMapper/Reducers/InitialValues';
import PaginationReducer from 'CategoryMapper/Reducers/PaginationReducer';
    

    var CombinedReducer = Redux.combineReducers({
        accounts: AccountsReducer,
        categories: CategoriesReducer,
        categoryMaps: CategoryMapsReducer,
        form: ReduxForm.reducer,
        initialValues: InitialValuesReducer,
        pagination: PaginationReducer
    });
    export default CombinedReducer;

