define([
    'react',
    'react-redux',
    'CategoryMapper/Components/CategoryMap',
], function(
    React,
    ReactRedux,
    CategoryMap
) {
    "use strict";

    var CategoryMapsComponent = React.createClass({
        renderCategoryMapComponents: function() {
            return this.props.categoryMap.map(function (categoryMap, index) {
                return <CategoryMap
                    accounts={categoryMap.categoryMap}
                    index={index}
                />
            });
        },
        render: function() {
            return (
                <span>
                    {this.renderCategoryMapComponents()}
                </span>
            );
        }
    });

    var mapStateToProps = function(state) {
        return {
            categoryMap: state.categoryMap
        }
    };

    var CategoryMapsConnector = ReactRedux.connect(mapStateToProps, null);

    return CategoryMapsConnector(CategoryMapsComponent);
});
