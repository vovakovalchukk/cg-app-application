define([
    'react',
    'CategoryMapper/Components/CategoryMap',
], function(
    React,
    CategoryMapContainer
) {
    "use strict";

    var RootComponent = React.createClass({
        submitCategoryMap: function(values) {
            /**
             *  @TODO: this will be handled by LIS-121, but I'll leave this debug code in here,
             *  @TODO: so that we know what are the form values when pressing the Save button
             * */
            console.log(values);
        },
        render: function()
        {
            return (
                <CategoryMapContainer
                    onSubmit={this.submitCategoryMap}
                />
            );
        }
    });

    return RootComponent;
});