define([
    'react',
    'Product/Components/Checkbox',
    'Common/Components/CurrencyInput',
    'Common/Components/ImagePicker'
], function(
    React,
    Checkbox,
    CurrencyInput,
    ImagePicker
) {
    "use strict";

    var SimpleProduct = React.createClass({
        getDefaultProps: function() {
            return {
                product: {},
                currency: '£',
                images: true,
                channelSpecificFields: {},
                setFormStateListing: function() {}
            }
        },
        onValueChange: function(fieldName, event) {
            var newStateObject = {};
            newStateObject[fieldName] = event.target.value;
            this.props.setFormStateListing(newStateObject);
        },
        renderChannelSpecificFields: function() {
            var channelSpecificFields = [];
            for (var fieldName in this.props.channelSpecificFields) {
                var channelSpecificField = this.props.channelSpecificFields[fieldName];
                
                channelSpecificFields.push(<label>
                    <span className={"inputbox-label"}>{channelSpecificField.displayName}</span>
                    <div className={"order-inputbox-holder"}>
                        {channelSpecificField.getFormComponent(this.props[fieldName], this.onValueChange.bind(this, fieldName))}
                    </div>
                </label>);
            }
            return channelSpecificFields;
        },
        onImageSelected: function(image, selectedImageIds) {
            this.props.setFormStateListing({
                imageId: image.id
            });
        },
        renderImagePicker: function() {
            if (this.props.product.images.length == 0) {
                return (
                    <p>No images available</p>
                );
            }
            return (
                <ImagePicker
                    name="image"
                    multiSelect={false}
                    images={this.props.product.images}
                    onImageSelected={this.onImageSelected}
                />
            );
        },
        render: function() {
            return (
                <div className={"simple-product"}>
                    <label>
                        <span className={"inputbox-label"}>Sku</span>
                        <div className={"order-inputbox-holder"}>
                            {this.props.product.sku}
                        </div>
                    </label>
                    {
                        this.props.images ? <label>
                            <span className={"inputbox-label"}>Image</span>
                            {this.renderImagePicker()}
                        </label>
                        : null
                    }
                    <label>
                        <span className={"inputbox-label"}>Price</span>
                        <div className={"order-inputbox-holder"}>
                            <CurrencyInput
                                value={this.props.price ? this.props.price: null}
                                onChange={function(event) { this.props.setFormStateListing({'price': event.target.value}); }.bind(this)}
                                currency={this.props.currency}
                            />
                        </div>
                    </label>
                    {this.renderChannelSpecificFields()}
                </div>
            );
        }
    });

    return SimpleProduct;
});
