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
                categoryId: null,
                itemSpecifics: {}
            };
        },
        render: function() {
            return (
                <div className="ebay-category-form-container">
                    <ItemSpecifics categoryId={this.props.categoryId} itemSpecifics={this.props.itemSpecifics} />
                </div>
            );
        }
    });
    return EbayCategoryFormComponent;
});