import {combineReducers} from 'redux';
import {reducer as reduxFormReducer} from 'redux-form';
import AccountsReducer from 'CategoryMapper/Reducers/Accounts';
import CategoryMapsReducer from 'CategoryMapper/Reducers/CategoryMaps';
import CategoriesReducer from 'CategoryMapper/Reducers/Categories';
import InitialValuesReducer from 'CategoryMapper/Reducers/InitialValues';
import PaginationReducer from 'CategoryMapper/Reducers/PaginationReducer';
    

    var CombinedReducer = combineReducers({
        accounts: AccountsReducer,
        categories: CategoriesReducer,
        categoryMaps: CategoryMapsReducer,
        form: reduxFormReducer,
        initialValues: InitialValuesReducer,
        pagination: PaginationReducer
    });
    export default CombinedReducer;

