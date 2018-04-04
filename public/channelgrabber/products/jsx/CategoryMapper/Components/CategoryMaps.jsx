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
            for (var index in this.props.categoryMaps) {
                categoryMaps.push(
                    <CategoryMap
                        accounts={this.props.categoryMaps[index]}
                        index={index}
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
        var categories = [];
        for (var accountId in state.accounts) {
            state.accounts[accountId].categories = [state.categories[accountId]];
        }
        return state.accounts;
    }

    var mapStateToProps = function(state) {
        return {
            categoryMaps: [mergeData(state)]
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
