define([
    'react'
], function(
    React
) {
    "use strict";

    var EbayCategoryFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                itemSpecifics: {}
            };
        },
        render: function() {
            return (
                <div className="ebay-category-form-container">
                    Ebay test
                </div>
            );
        }
    });
    return EbayCategoryFormComponent;
});