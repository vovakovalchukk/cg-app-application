define([
    'react',
    './Ebay/ListingDuration',
    './Ebay/ItemSpecifics'
], function(
    React,
    ListingDuration,
    ItemSpecifics
) {
    "use strict";

    var EbayCategoryFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                categoryId: null,
                listingDuration: {},
                itemSpecifics: {}
            };
        },
        render: function() {
            return (
                <div className="ebay-category-form-container">
                    <ListingDuration listingDurations={this.props.listingDuration} />
                    <ItemSpecifics categoryId={this.props.categoryId} itemSpecifics={this.props.itemSpecifics} />
                </div>
            );
        }
    });
    return EbayCategoryFormComponent;
});