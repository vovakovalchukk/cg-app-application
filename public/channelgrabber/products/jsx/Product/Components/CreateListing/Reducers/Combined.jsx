import Redux from 'redux';
import ReduxForm from 'redux-form';
import CategoryMapsReducer from 'CategoryMapper/Reducers/CategoryMaps';
import CategoriesReducer from 'CategoryMapper/Reducers/Categories';
import AccountsReducer from 'Product/Components/CreateListing/Reducers/Accounts';
import AddNewCategoryVisibleReducer from 'Product/Components/CreateListing/Reducers/AddNewCategoryVisible';
import CategoryTemplateOptionsReducer from 'Product/Components/CreateListing/Reducers/CategoryTemplateOptions';
import AccountSettingsReducer from 'Product/Components/CreateListing/Reducers/AccountSettingsReducer';
    

    var CombinedReducer = Redux.combineReducers({
        initialValues: function (state) {
            return state ? state : {};
        },
        categoryTemplateOptions: CategoryTemplateOptionsReducer,
        form: ReduxForm.reducer,
        accounts: AccountsReducer,
        categories: CategoriesReducer,
        categoryMaps: CategoryMapsReducer,
        addNewCategoryVisible: AddNewCategoryVisibleReducer,
        accountSettings: AccountSettingsReducer
    });

    export default CombinedReducer;

