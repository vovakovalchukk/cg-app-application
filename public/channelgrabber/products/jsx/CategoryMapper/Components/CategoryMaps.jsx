import React from 'react';
import {connect} from 'react-redux';
import CategoryMap from 'CategoryMapper/Components/CategoryMap';
import Actions from 'CategoryMapper/Actions/Actions';
import SearchComponent from 'CategoryMapper/Components/Search';
import LoadMoreButton from 'CategoryMapper/Components/LoadMoreButton';


class CategoryMapsComponent extends React.Component {
    componentDidMount() {
        this.props.fetchCategoryMaps();
    }

    renderNewCategoryMapComponent = () => {
        return this.renderCategoryMapComponent(0);
    };

    renderExistingCategoryMapComponents = () => {
        if (Object.keys(this.props.categoryMaps).length <= 1 && !this.props.pagination.isFetching) {
            return <span>No category maps found.</span>
        }
        var categoryMaps = [];
        for (var mapId in this.props.categoryMaps) {
            if (mapId == 0) {
                continue;
            }
            categoryMaps.push(this.renderCategoryMapComponent(mapId));
        }
        return categoryMaps;
    };

    renderCategoryMapComponent = (mapId) => {
        return (
            <CategoryMap
                accounts={this.props.categoryMaps[mapId].accounts}
                mapId={mapId}
                onSubmit={this.props.onSubmit}
                form={'categoryMap-' + mapId}
                key={'categoryMap-' + mapId}
                onViewExistingMapClick={this.props.fetchCategoryMaps}
            />
        );
    };

    renderSearchBox = () => {
        return <SearchComponent
            value={this.props.pagination.searchText}
            onSubmit={this.props.fetchCategoryMaps}
            disabled={this.props.pagination.isFetching}
        />
    };

    renderLoadMoreButton = () => {
        return <LoadMoreButton
            onClick={this.props.fetchCategoryMaps.bind(this, this.props.pagination.searchText, this.props.pagination.page)}
            disabled={!this.props.pagination.loadMoreEnabled}
            active={this.props.pagination.loadMoreVisible}
        />
    };

    render() {
        return (
            <span>
                {this.renderNewCategoryMapComponent()}
                {this.renderSearchBox()}
                {this.renderExistingCategoryMapComponents()}
                {this.renderLoadMoreButton()}
            </span>
        );
    }
}

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

var mapDispatchToProps = function (dispatch) {
    return {
        fetchCategoryMaps: function (searchText = '', page = 1) {
            dispatch(Actions.fetchCategoryMaps(dispatch, searchText, page));
        }
    };
};

var CategoryMapsConnector = connect(mapStateToProps, mapDispatchToProps);

export default CategoryMapsConnector(CategoryMapsComponent);

