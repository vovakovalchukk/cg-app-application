import React from 'react';
import Checkbox from 'Product/Components/Checkbox';
import CurrencyInput from 'Common/Components/CurrencyInput';
import ImagePicker from 'Common/Components/ImagePicker';
    

    var SimpleProduct = React.createClass({
        getDefaultProps: function() {
            /**
             * ensure you pass in the value of the custom fields by props
             * i.e. if the Ebay form instantiates SimpleProduct with an ean field, then you should pass the value
             * of the ean field in via props when SimpleProduct is rendered
             *
             * @customFields - object -
             * {
             *     String <key>: {
                    displayName: String <displayName>,
                    getFormComponent: Fnc (value, onChange) return: ReactComponent <componentForCustomField>,
                    * note - onChange is a callback which must be used as the onChange for the component which you return
                    getDefaultValueFromVariation: Fnc(variation) return String <valueOfCustomFieldGivenAVariation>
                }
             * }
             *
             */
            return {
                product: {},
                currency: '£',
                images: true,
                customFields: {},
                setFormStateListing: function() {}
            }
        },
        onValueChange: function(fieldName, event) {
            var newStateObject = {};
            newStateObject[fieldName] = event.target.value;
            this.props.setFormStateListing(newStateObject);
        },
        renderCustomFields: function() {
            var customFields = [];
            for (var fieldName in this.props.customFields) {
                var channelSpecificField = this.props.customFields[fieldName];

                customFields.push(<label>
                    <span className={"inputbox-label"}>{channelSpecificField.displayName}</span>
                    <div className={"order-inputbox-holder"}>
                        {channelSpecificField.getFormComponent(this.props[fieldName], this.onValueChange.bind(this, fieldName))}
                    </div>
                </label>);
            }
            return customFields;
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
                    {this.renderCustomFields()}
                </div>
            );
        }
    });

    export default SimpleProduct;

