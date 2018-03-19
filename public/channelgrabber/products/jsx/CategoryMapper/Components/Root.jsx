define([
    'react',
    'CategoryMapper/Containers/CategoryMap',
], function(
    React,
    CategoryMapContainer
) {
    "use strict";

    var RootComponent = React.createClass({
        submitCategoryMap: function(values) {
            console.log(values);
        },
        render: function()
        {
            return (
                <CategoryMapContainer
                    onSubmit={this.submitCategoryMap}
                    accounts={this.props.accounts}
                />
            );
        }
    });

    return RootComponent;
});