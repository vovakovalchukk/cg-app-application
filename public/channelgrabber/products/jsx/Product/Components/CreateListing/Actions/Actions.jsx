import ApiHelper from 'CategoryMapper/Actions/ApiHelper';
import ResponseActions from 'Product/Components/CreateListing/Actions/ResponseActions';
    

    export default {
        fetchCategoryRoots: function(dispatch) {
            $.get(
                ApiHelper.buildFetchCategoryRootsUrl(),
                function(response) {
                    dispatch(ResponseActions.categoryRootsFetched(response));
                }
            );

            return {
                type: "FETCH_CATEGORY_ROOTS",
                payload: {}
            };
        },
        showAddNewCategoryMapComponent: function() {
            return {
                type: "SHOW_ADD_NEW_CATEGORY_MAP",
                payload: {}
            };
        },
        hideAddNewCategoryMapComponent: function() {
            return {
                type: "HIDE_NEW_CATEGORY_MAP",
                payload: {}
            };
        },
        categoryMapSelected: function(categoryIds) {
            return {
                type: "CATEGORY_MAP_SELECTED",
                payload: {
                    categoryIds: categoryIds
                }
            };
        },
        categoryMapSelectedByName: function(categoryName) {
            return {
                type: "CATEGORY_MAP_SELECTED_BY_NAME",
                payload: {
                    name: categoryName
                }
            };
        },
        fetchSettingsForAccount: function(accountId, dispatch) {
            console.log(' in fetchSettingsForaCCOUNT');
            
            
            $.ajax({
                context: this,
                url: '/products/create-listings/' + accountId + '/default-settings',
                type: 'GET',
                success: function(response) {
                    if (response.error == 'NO_SETTINGS') {
                        dispatch(ResponseActions.noAccountSettings(accountId));
                        return;
                    }

                    dispatch(ResponseActions.accountSettingsFetched(accountId, response));
                }
            });

            return {
                type: "FETCH_SETTINGS_FOR_ACCOUNT",
                payload: {
                    accountId: accountId
                }
            };
        }
    };

