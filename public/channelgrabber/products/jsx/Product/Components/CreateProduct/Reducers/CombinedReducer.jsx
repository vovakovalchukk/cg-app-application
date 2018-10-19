import {combineReducers} from 'redux';
import {reducer as reduxFormReducer} from 'redux-form';
import imageUploaderReducer from 'Common/Components/ImageUploader/ImageUploaderReducer';
import AccountReducer from 'Product/Components/CreateProduct/Reducers/AccountReducer';
import VariationsTableReducer from 'Product/Components/CreateProduct/Reducers/VariationsReducer';

    var AppReducer = combineReducers({
        form: reduxFormReducer,
        account: AccountReducer,
        variationsTable: VariationsTableReducer,
        uploadedImages: imageUploaderReducer
    });

    const CombinedReducer = (state, action) => {
        if (action.type === 'USER_LEAVES_CREATE_PRODUCT') {
            // setting state as undefined triggers Redux to use the initial values of all reducers
            state = undefined;
        }
        return AppReducer(state, action)
    };

    export default CombinedReducer;
