define([
    'react',
    'redux-form',
    'react-redux',
    'CategoryMapper/Components/CategoryMap',
    'CategoryMapper/Actions/Actions',
    'CategoryMapper/Components/Search',
], function(
    React,
    ReduxForm,
    ReactRedux,
    CategoryMap,
    Actions,
    SearchComponent
) {
    "use strict";

    var Field = ReduxForm.Field;
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
                        onCategorySelected={this.props.onCategorySelected}
                        onRefreshClick={this.props.onRefreshClick}
                        onRemoveButtonClick={this.props.onRemoveButtonClick}
                        onSubmit={this.props.onSubmit}
                    />
                );

                if (mapId == 0) {
                    categoryMaps.push(this.renderSearchBox());
                }
            }
            return categoryMaps;
        },
        renderSearchBox: function() {
            return <SearchComponent
                value={this.props.pagination.searchText}
                onSubmit={this.props.fetchCategoryMaps}
            />
        },
        renderLoadMoreButton: function() {
            return <form
                onSubmit={function(event) {event.preventDefault();}}
                name={'loadMore'}
            >
                <div className={"order-form half product-container category-map-container"}>
                    <div>
                        <label className={"save-button"}>
                            <div className={"button container-btn yes"} onClick={this.props.fetchCategoryMaps.bind(this, this.props.pagination.searchText, this.props.pagination.page)}>
                                <span>Load more...</span>
                            </div>
                        </label>
                    </div>
                </div>
            </form>
        },
        render: function() {
            return (
                <span>
                    {this.renderCategoryMapComponents()}
                    {this.renderLoadMoreButton()}
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

    var mapStateToProps = function(state) {
        return {
            categoryMaps: mergeData(state),
            pagination: state.pagination
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
