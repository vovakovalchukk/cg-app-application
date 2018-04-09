define([
    'react',
    'redux-form',
    'react-redux',
    'CategoryMapper/Components/CategoryMap',
    'CategoryMapper/Actions/Actions',
], function(
    React,
    ReduxForm,
    ReactRedux,
    CategoryMap,
    Actions
) {
    "use strict";

    var CategoryMapsComponent = React.createClass({
        componentDidMount: function () {
            this.props.fetchCategoryMaps();
        },
        renderCategoryMapComponents: function() {
            var categoryMaps = [];
            for (var mapId in this.props.categoryMaps) {
                categoryMaps.push(
                    <CategoryMap
                        accounts={this.props.categoryMaps[mapId].accounts}
                        mapId={mapId}
                        name={this.props.categoryMaps[mapId].name}
                        etag={this.props.categoryMaps[mapId].etag}
                        onCategorySelected={this.props.onCategorySelected}
                        onRefreshClick={this.props.onRefreshClick}
                        onRemoveButtonClick={this.props.onRemoveButtonClick}
                        handleSubmit={this.props.handleSubmit}
                    />
                );
            }
            return categoryMaps;
        },
        render: function() {
            return (
                <span>
                    <form onSubmit={this.props.handleSubmit}>
                        {this.renderCategoryMapComponents()}
                    </form>
                </span>
            );
        }
    });


    var categoryMapsFormCreator = ReduxForm.reduxForm({
        form: "categoryMapssss",
        enableReinitialize: true,
        keepDirtyOnReinitialize: true
    });

    CategoryMapsComponent = categoryMapsFormCreator(CategoryMapsComponent);

    var mergeData = function (state) {
        var categories = {},
            categoryMaps = {},
            accountId;

        for (accountId in state.accounts) {
            categories[accountId] = Object.assign({}, state.accounts[accountId], {
                categories: Object.assign({}, state.categories[accountId])
            });
        }

        if (!(0 in state.categoryMaps)) {
            categoryMaps[0] = {
                accounts: categories,
                name: '',
                etag: ''
            };
        }

        var categoriesForMap;
        for (var mapId in state.categoryMaps) {
            categoriesForMap = Object.assign({}, categories);
            for (accountId in state.categoryMaps[mapId].selectedCategories) {
                categoriesForMap[accountId] = Object.assign({}, categoriesForMap[accountId]);
                categoriesForMap[accountId].selectedCategories = state.categoryMaps[mapId].selectedCategories[accountId].slice();
            }

            categoryMaps[mapId] =  {
                accounts: categoriesForMap,
                name: state.categoryMaps[mapId].name,
                etag: state.categoryMaps[mapId].etag
            };
        }

        return categoryMaps;
    }

    var formatFormData = function(state) {
        var categoryMaps = state.categoryMaps,
            categoryMap,
            data = [],
            categoriesForAccount,
            categoryId,
            categories;

        for (var mapId in categoryMaps) {
            categoryMap = categoryMaps[mapId];
            categories = [];

            for (var accountId in categoryMap.selectedCategories) {
                categoriesForAccount = categoryMap.selectedCategories[accountId];
                categoryId = categoriesForAccount[categoriesForAccount.length - 1];
                categories[accountId] = categoryId;
            }

            data[mapId] = {
                name: categoryMap.name,
                etag: categoryMap.etag,
                categories: categories
            }
        }

        return data;
    }

    var mapStateToProps = function(state) {
        return {
            categoryMaps: mergeData(state),
            initialValues: {
                map: formatFormData(state)
            }
        }
    };

    var mapDispatchToProps = function (dispatch, ownProps) {
        return {
            onCategorySelected: function(mapId, accountId, categoryId, categoryLevel, selectedCategories) {
                dispatch(Actions.categorySelected(dispatch, mapId, accountId, categoryId, categoryLevel, selectedCategories));
            },
            onRefreshClick: function(accountId) {
                dispatch(Actions.refreshButtonClicked(dispatch, accountId));
            },
            onRemoveButtonClick: function (mapId, accountId) {
                dispatch(Actions.removeButtonClicked(mapId, accountId));
            },
            fetchCategoryMaps: function () {
                dispatch(Actions.fetchCategoryMaps(dispatch));
            }
        };
    };

    var CategoryMapsConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);

    return CategoryMapsConnector(CategoryMapsComponent);
});
