define([
    'react',
    'redux-form',
    'CategoryMapper/Components/CategoryMaps',
    'CategoryMapper/Actions/ResponseActions',
    'CategoryMapper/Actions/ApiHelper',
], function(
    React,
    ReduxForm,
    CategoryMaps,
    Actions,
    ApiHelper
) {
    "use strict";

    var SubmissionError = ReduxForm.SubmissionError;

    var RootComponent = React.createClass({
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
                if (accountData.selectedCategories) {
                    accountData.selectedCategories.forEach(function (selectedCatId) {
                        lastSelectedCategory = categories[selectedCatId];
                        if (categories[selectedCatId] && categories[selectedCatId].categoryChildren && Object.keys(categories[selectedCatId].categoryChildren).length > 0) {
                            categories = categories[selectedCatId].categoryChildren;
                        }
                    });
                    if (lastSelectedCategory && !lastSelectedCategory.listable) {
                        invalidAccountIds[accountId] = accountId;
                    }
                }
            });

            if (Object.keys(invalidAccountIds).length > 0) {
                return {
                    text: 'The selected category is not listable. Please select another one.',
                    accountIds: invalidAccountIds
                }
            }

            return null;
        },
        validateForm: function(mapId, values, state) {
            var name = this.validateName(values.name),
                categories = this.validateCategories(values.categories, state.accounts),
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
            if (response.error) {
                if (response.error.code == 'existing name') {
                    throw new SubmissionError({name: response.error.message});
                }
                if (response.error.code == 'existing category') {
                    throw new SubmissionError({
                        categories: {
                            _error: JSON.stringify({
                                text: response.error.message,
                                existingMapNames: this.extractExistingCategoryNamesFromErrorResponse(response.error)
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
            }
        },
        extractExistingCategoryNamesFromErrorResponse: function(error) {
            var existing = {};
            error.existing.forEach(function(categoryMap) {
                existing[categoryMap.accountId] = categoryMap.name;
            });
            return existing;
        },
        formatAccountCategoryData: function(categories) {
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
                categoryIds: this.formatAccountCategoryData(values.categories)
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
                    this.buildPostData(mapId, values)
                ).success(function(response) {
                    resolve(response);
                }).error(function() {
                    n.error('There was an error while saving the category map. Please try again or contact support if the problem persists.');
                });
            }.bind(this));
        },
        submitCategoryMap: function(values, dispatch, state) {
            var mapId = state.mapId;
            this.validateForm(mapId, values, state);

            return this.saveCategoryMap(mapId, values).then(function(response) {
                this.checkResponseForErrors(response);

                if (mapId == 0) {
                    n.success('The new category map <b>' + values.name + '</b> has been saved successfully');
                    dispatch(Actions.addCategoryMap(response.id, response.etag, values));
                    dispatch(ReduxForm.reset(state.form));
                    return;
                }

                n.success('The category map <b>' +  values.name + '</b> has been successfully updated.');
                dispatch(Actions.updateCategoryMap(mapId, response.etag, values));
            }.bind(this));
        },
        render: function()
        {
            return (
                <CategoryMaps
                    onSubmit={this.submitCategoryMap}
                />
            );
        }
    });

    return RootComponent;
});