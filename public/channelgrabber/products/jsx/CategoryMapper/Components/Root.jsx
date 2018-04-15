define([
    'react',
    'redux-form',
    'CategoryMapper/Components/CategoryMaps',
    'CategoryMapper/Actions/ResponseActions',
], function(
    React,
    ReduxForm,
    CategoryMaps,
    Actions
) {
    "use strict";

    var SubmissionError = ReduxForm.SubmissionError;

    var saveCategoryMap = function(values) {
        var extractCategoryIds = function(categories) {
            var categoryIds = [];
            categories.forEach(function(categoryId, accountId) {
                if (categoryId) {
                    categoryIds.push(categoryId);
                }
            });
            return categoryIds;
        }
        return new Promise(function(resolve) {
            return $.post(
                '/settings/category/templates/save',
                {
                    name: values.name,
                    etag: values.etag,
                    categoryIds: extractCategoryIds(values.categories)
                }
            ).success(
                function (response) {
                    resolve(response);
                }
            );
        });
    };

    var RootComponent = React.createClass({
        validateForm: function(mapId, values) {
            if (!values.name || values.name.length < 3) {
                throw new SubmissionError({name: 'The name is too short.'});
            }
        },
        submitCategoryMap: function(values, dispatch, state) {
            var mapId = state.mapId;
            this.validateForm(mapId, values);
            /**
             *  @TODO: this will be handled by LIS-121, but I'll leave this debug code in here,
             *  @TODO: so that we know what are the form values when pressing the Save button
             * */
            console.log({
                dispatch: dispatch,
                values: values,
                state: state
            });

            return saveCategoryMap(values).then(function(response) {
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

                if (mapId == 0) {
                    dispatch(Actions.addCategoryMap(response.id, response.etag, values));
                    return;
                }

                dispatch(Actions.updateCategoryMap(mapId, response.etag, values));
            });
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