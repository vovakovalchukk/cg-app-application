define([
    'react',
    './Ebay/ItemSpecifics'
], function(
    React,
    ItemSpecifics
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
                    <ItemSpecifics itemSpecifics={this.props.itemSpecifics} />
                </div>
            );
        }
    });
    return EbayCategoryFormComponent;
});