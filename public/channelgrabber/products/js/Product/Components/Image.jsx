define([
    'react'
], function(
    React
) {
    "use strict";

    var ImageComponent = React.createClass({
        render: function()
        {
            var imageUrl = this.props.images.length > 0 ? this.props.images[0]['url'] : this.props.imageBasePath + '/noproductsimage.png';
            return (
                <div className="product-holder">
                    <div className="product-image-container">
                        <div className="product-image">
                            <span>
                                <img src={imageUrl} />
                            </span>
                        </div>
                    </div>
                </div>
            );
        }
    });

    return ImageComponent;
});