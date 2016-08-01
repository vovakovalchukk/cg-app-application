define([
    'react'
], function(
    React
) {
    "use strict";

    var ImageComponent = React.createClass({
        render: function()
        {
            return (
                <div className="product-holder">
                    <div className="product-image-container">
                        <div className="product-image">
                            <span>
                                <img src={this.props.url} />
                            </span>
                        </div>
                    </div>
                </div>
            );
        }
    });

    return ImageComponent;
});