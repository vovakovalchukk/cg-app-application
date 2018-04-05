define([
    'react',
    'react-redux',
    'CategoryMapper/Components/CategoryMap',
    'CategoryMapper/Actions/Category',
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
                        index={mapId}
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

        // console.log(categoryMaps);

        return categoryMaps;
    }

    var mapStateToProps = function(state) {
        return {
            categoryMaps: mergeData(state)
        }
    };

    var mapDispatchToProps = function (dispatch) {
        return {
            fetchCategoryMaps: function () {
                dispatch(Actions.fetchCategoryMaps(dispatch));
            }
        };
    };

    var CategoryMapsConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);

    return CategoryMapsConnector(CategoryMapsComponent);
});
