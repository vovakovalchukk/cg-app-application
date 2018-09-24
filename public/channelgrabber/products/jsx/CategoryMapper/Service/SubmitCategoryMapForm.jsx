import {SubmissionError, reset as reduxFormReset} from 'redux-form';
import ApiHelper from 'CategoryMapper/Actions/ApiHelper';
import Actions from 'CategoryMapper/Actions/ResponseActions';

    var service = {
        validateName: function(name) {
            if (!name || name.length < 2) {
                return 'The name is too short.';
            }

            return null;
        },
        validateCategories: function(categories, accounts) {
            var accountData,
                invalidAccountIds = {};

            categories.forEach(function (leafCategoryId, accountId) {
                accountData = accounts[accountId];
                var categories = accountData.categories,
                    lastSelectedCategory;

                if (!accountData.selectedCategories) {
                    return;
                }

                accountData.selectedCategories.forEach(function (selectedCatId) {
                    lastSelectedCategory = categories[selectedCatId];
                    if (categories[selectedCatId] && categories[selectedCatId].categoryChildren && Object.keys(categories[selectedCatId].categoryChildren).length > 0) {
                        categories = categories[selectedCatId].categoryChildren;
                    }
                });

                if (lastSelectedCategory && !lastSelectedCategory.listable) {
                    invalidAccountIds[accountId] = accountId;
                }
            });

            if (Object.keys(invalidAccountIds).length > 0) {
                return {
                    text: 'The selected category is not listable. Please select another one.',
                    accountIds: invalidAccountIds
                };
            }

            return null;
        },
        validateForm: function(mapId, values, state) {
            var name = service.validateName(values.name),
                categories = service.validateCategories(values.categories, state.accounts),
                errorObject = {};

            if (name) {
                errorObject.name = name;
            }
            if (categories) {
                errorObject.categories = {
                    _error: JSON.stringify(categories)
                }
            }

            if (values.categories.filter(Boolean).length == 0) {
                errorObject._error = 'Please select at least one category.'
            }

            if (Object.keys(errorObject).length > 0) {
                throw new SubmissionError(errorObject);
            }
        },
        checkResponseForErrors: function(response) {
            if (!response.error) {
                return;
            }

            if (response.error.code == 'existing name') {
                throw new SubmissionError({name: response.error.message});
            }

            if (response.error.code == 'existing category') {
                throw new SubmissionError({
                    categories: {
                        _error: JSON.stringify({
                            text: response.error.message,
                            existingMapNames: service.extractExistingCategoryNamesFromErrorResponse(response.error)
                        })
                    }
                });
            }

            if (response.error.code) {
                throw new SubmissionError({
                    _error: response.error.message
                });
            }

            n.error('There was an error while saving the category map. Please try again or contact support if the problem persists.');
        },
        extractExistingCategoryNamesFromErrorResponse: function(error) {
            var existing = {};
            error.existing.forEach(function(categoryMap) {
                existing[categoryMap.accountId] = categoryMap.name;
            });
            return existing;
        },
        filterSelectedCategoryIds: function(categories) {
            var accounts = {};
            categories.forEach(function(categoryId, accountId) {
                if (categoryId) {
                    accounts[accountId] = categoryId;
                }
            });
            return accounts;
        },
        buildPostData: function(mapId, values) {
            var data = {
                name: values.name,
                categoryIds: this.filterSelectedCategoryIds(values.categories)
            };
            if (mapId > 0) {
                data.id = mapId;
                data.etag = values.etag;
            }
            return data;
        },
        saveCategoryMap: function(mapId, values) {
            return new Promise(function(resolve) {
                return $.post(
                    ApiHelper.buildSaveCategoryMapUrl(),
                    service.buildPostData(mapId, values)
                ).success(function(response) {
                    resolve(response);
                }).error(function() {
                    n.error('There was an error while saving the category map. Please try again or contact support if the problem persists.');
                });
            });
        },
    };

    export default function(values, dispatch, state) {
        var mapId = state.mapId;
        service.validateForm(mapId, values, state);

        return service.saveCategoryMap(mapId, values).then(function(response) {
            service.checkResponseForErrors(response);

            if (mapId == 0) {
                n.success('The new category map <b>' + values.name + '</b> has been saved successfully');
                dispatch(Actions.addCategoryMap(response.id, response.etag, values));
                dispatch(reduxFormReset(state.form));
                return;
            }

            n.success('The category map <b>' +  values.name + '</b> has been successfully updated.');
            dispatch(Actions.updateCategoryMap(mapId, response.etag, values));
        });
    }

