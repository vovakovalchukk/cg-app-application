define([
    'react',
    'react-redux',
    'CategoryMapper/Components/CategoryMap',
    'CategoryMapper/Actions/Actions',
], function(
    React,
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
                        accounts={this.props.categoryMaps[mapId]}
                        mapId={mapId}
                        onCategorySelected={this.props.onCategorySelected}
                        onRefreshClick={this.props.onRefreshClick}
                        onRemoveButtonClick={this.props.onRemoveButtonClick}
                    />
                );
            }
            return categoryMaps;
        },
        render: function() {
            return (
                <span>
                    {this.renderCategoryMapComponents()}
                </span>
            );
        }
    });

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
            categoryMaps[0] = categories;
        }

        var categoriesForMap;
        for (var mapId in state.categoryMaps) {
            categoriesForMap = Object.assign({}, categories);
            for (accountId in state.categoryMaps[mapId].selectedCategories) {
                categoriesForMap[accountId].selectedCategories = state.categoryMaps[mapId].selectedCategories[accountId];
            }

            categoryMaps[mapId] = categoriesForMap;
        }

        return categoryMaps;
    }

    var mapStateToProps = function(state) {
        return {
            categoryMaps: mergeData(state)
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
            onRemoveButtonClick: function (accountId) {
                dispatch(Actions.removeButtonClicked(ownProps.index, accountId));
            },
            fetchCategoryMaps: function () {
                dispatch(Actions.fetchCategoryMaps(dispatch));
            }
        };
    };

    var CategoryMapsConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);

    return CategoryMapsConnector(CategoryMapsComponent);
});
