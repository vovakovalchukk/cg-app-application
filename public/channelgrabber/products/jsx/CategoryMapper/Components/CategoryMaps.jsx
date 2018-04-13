define([
    'react',
    'react-redux',
    'CategoryMapper/Components/CategoryMap',
    'CategoryMapper/Actions/Actions',
    'CategoryMapper/Components/Search',
    'CategoryMapper/Components/LoadMoreButton',
], function(
    React,
    ReactRedux,
    CategoryMap,
    Actions,
    SearchComponent,
    LoadMoreButton
) {
    "use strict";

    var CategoryMapsComponent = React.createClass({
        componentDidMount: function () {
            this.props.fetchCategoryMaps();
        },
        renderNewCategoryMapComponent: function() {
            return this.renderCategoryMapComponent(0);
        },
        renderExistingCategoryMapComponents: function() {
            var categoryMaps = [];
            for (var mapId in this.props.categoryMaps) {
                if (mapId == 0) {
                    continue;
                }
                categoryMaps.push(this.renderCategoryMapComponent(mapId));
            }
            return categoryMaps;
        },
        renderCategoryMapComponent: function(mapId) {
            return (
                <CategoryMap
                    accounts={this.props.categoryMaps[mapId].accounts}
                    mapId={mapId}
                    onSubmit={this.props.onSubmit}
                    form={'categoryMap-' + mapId}
                    key={'categoryMap-' + mapId}
                />
            );
        },
        renderSearchBox: function() {
            return <SearchComponent
                value={this.props.pagination.searchText}
                onSubmit={this.props.fetchCategoryMaps}
                disabled={this.props.pagination.isFetching}
            />
        },
        renderLoadMoreButton: function() {
            return <LoadMoreButton
                onClick={this.props.fetchCategoryMaps.bind(this, this.props.pagination.searchText, this.props.pagination.page)}
                disabled={!this.props.pagination.loadMoreEnabled}
                active={this.props.pagination.loadMoreVisible}
            />
        },
        render: function() {
            return (
                <span>
                    {this.renderNewCategoryMapComponent()}
                    {this.renderSearchBox()}
                    {this.renderExistingCategoryMapComponents()}
                    {this.renderLoadMoreButton()}
                </span>
            );
        }
    });

    var addNewCategoryMapTemplate = function(existingCategoryMaps, newCategoryMaps, categories) {
        if (!(0 in existingCategoryMaps)) {
            newCategoryMaps[0] = {
                accounts: categories,
                name: '',
                etag: ''
            };
        }
    }

    var addExistingCategoryMaps = function(existingCategoryMaps, newCategoryMaps, categories) {
        var categoriesForMap;
        for (var mapId in existingCategoryMaps) {
            categoriesForMap = Object.assign({}, categories);
            for (var accountId in existingCategoryMaps[mapId].selectedCategories) {
                categoriesForMap[accountId] = Object.assign({}, categoriesForMap[accountId]);
                categoriesForMap[accountId].selectedCategories = existingCategoryMaps[mapId].selectedCategories[accountId].slice();
            }

            newCategoryMaps[mapId] =  {
                accounts: categoriesForMap,
                name: existingCategoryMaps[mapId].name,
                etag: existingCategoryMaps[mapId].etag
            };
        }
    }

    var convertStateToCategoryMaps = function (state) {
        var categories = {},
            categoryMaps = {},
            accountId;

        for (accountId in state.accounts) {
            categories[accountId] = Object.assign({}, state.accounts[accountId], {
                categories: Object.assign({}, state.categories[accountId])
            });
        }

        addNewCategoryMapTemplate(state.categoryMaps, categoryMaps, categories);
        addExistingCategoryMaps(state.categoryMaps, categoryMaps, categories);

        return categoryMaps;
    }

    var mapStateToProps = function(state) {
        return {
            categoryMaps: convertStateToCategoryMaps(state),
            pagination: state.pagination
        }
    };

    var mapDispatchToProps = function (dispatch, ownProps) {
        return {
            fetchCategoryMaps: function (searchText = '', page = 1) {
                dispatch(Actions.fetchCategoryMaps(dispatch, searchText, page));
            },
            searchTextChanged: function (searchText) {
                dispatch(Actions.updateSearch(searchText));
            }
        };
    };

    var CategoryMapsConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);

    return CategoryMapsConnector(CategoryMapsComponent);
});
