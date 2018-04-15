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
        validateForm: function(mapId, values) {
            if (!values.name || values.name.length < 2) {
                throw new SubmissionError({name: 'The name is too short.'});
            }
        },
        checkResponseForErrors: function(response) {
            if (response.error) {
                if (response.error.code == 'existing name') {
                    throw new SubmissionError({name: response.error.message});
                }
                if (response.error.code == 'existing category') {
                    // This doesn't work as expected as it triggers the whole array of accounts to fail, but it's a start
                    throw new SubmissionError({
                        categories: {
                            _error: response.error.message
                        }
                    });
                }
            }
        },
        extractCategoryIdsFromFormValues: function(categories) {
            var categoryIds = [];
            categories.forEach(function(categoryId, accountId) {
                if (categoryId) {
                    categoryIds.push(categoryId);
                }
            });
            return categoryIds;
        },
        saveCategoryMap: function(values) {
            return new Promise(function(resolve) {
                return $.post(
                    ApiHelper.buildSaveCategoryMapUrl(),
                    {
                        name: values.name,
                        etag: values.etag,
                        categoryIds: this.extractCategoryIdsFromFormValues(values.categories)
                    }
                ).success(function(response) {
                    resolve(response);
                }).error(function() {
                    n.error('There was an error while saving the category map. Please try again or contact support if the problem persists.');
                })
            }.bind(this));
        },
        submitCategoryMap: function(values, dispatch, state) {
            var mapId = state.mapId;
            this.validateForm(mapId, values);

            console.log(mapId, values);

            return this.saveCategoryMap(values).then(function(response) {
                this.checkResponseForErrors(response);

                if (mapId == 0) {
                    n.success('The new category map <b>' + values.name + '</b> has been saved successfully');
                    dispatch(Actions.addCategoryMap(response.id, response.etag, values));
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