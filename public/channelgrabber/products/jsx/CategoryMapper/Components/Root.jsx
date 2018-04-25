define([
    'react',
    'CategoryMapper/Components/CategoryMaps',
    'CategoryMapper/Service/SubmitCategoryMapForm'
], function(
    React,
    CategoryMaps,
    submitCategoryMapForm
) {
    "use strict";

    var RootComponent = React.createClass({
        render: function()
        {
            console.log(submitCategoryMapForm);
            return (
                <CategoryMaps
                    onSubmit={submitCategoryMapForm}
                />
            );
        }
    });

    return RootComponent;
});