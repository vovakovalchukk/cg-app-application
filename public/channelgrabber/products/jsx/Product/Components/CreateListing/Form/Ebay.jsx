define([
    'react',
    'Common/Components/Select'
], function(
    React,
    Select
) {
    "use strict";

    var EbayComponent = React.createClass({
        render: function() {
            return <div>
                <div>
                    <div>Listing Title</div>
                    <div></div>
                </div>
                <div>
                    <div>Price</div>
                    <div></div>
                </div>
                <div>
                    <div>Description</div>
                    <div></div>
                </div>
            </div>;
        }
    });

    return EbayComponent;
});