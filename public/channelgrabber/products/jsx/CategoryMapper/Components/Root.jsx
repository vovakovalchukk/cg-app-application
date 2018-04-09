define([
    'react',
    'CategoryMapper/Components/CategoryMaps',
], function(
    React,
    CategoryMaps
) {
    "use strict";

    var RootComponent = React.createClass({
        submitCategoryMap: function(values, f, state) {
            /**
             *  @TODO: this will be handled by LIS-121, but I'll leave this debug code in here,
             *  @TODO: so that we know what are the form values when pressing the Save button
             * */
            console.log({
                values: values,
                state: state
            });
        },
        render: function()
        {
            return (
                <CategoryMaps
                    onSubmit={this.submitCategoryMap}
                />
            );
        }
    });

    return RootComponent;
});