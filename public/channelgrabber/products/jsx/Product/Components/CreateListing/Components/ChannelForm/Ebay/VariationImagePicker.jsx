define([
    'react',
    'react-redux',
    'redux-form',
    'Common/Components/Select',
    'Common/Components/ImagePicker'
], function(
    React,
    ReactRedux,
    ReduxForm,
    Select,
    ImagePicker
) {
    "use strict";

    var Field = ReduxForm.Field;

    var VariationImagePicker =  React.createClass({
        getInitialState: function() {
            return {
                selectedAttributeName: null,
                selectedAttributeValues: [],
                attributeNameSelectField: null
            }
        },
        getDefaultProps: function() {
            return {
                product: {},
                variationsDataForProduct: {},
                attributeNames: {}
            };
        },
        formatAttributeNamesOptions: function() {
            return this.props.product.attributeNames.map(name => {
                return {
                    name: name,
                    value: name
                }
            });
        },
        renderAttributeNameSelectComponent: function(field) {
            if (!this.state.attributeNameSelectField) {
                this.onAttributeNameSelected(field.input, {value: this.props.product.attributeNames[0]});
                this.setState({
                    attributeNameSelectField: field
                });
            }
            return <label className="input-container">
                <span className={"inputbox-label"}>{field.displayTitle}</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        autoSelectFirst={true}
                        onOptionChange={this.onAttributeNameSelected.bind(this, field.input)}
                        options={field.options}
                        selectedOption={this.findSelectedOption(field.input.value, field.options)}
                    />
                </div>
            </label>;
        },
        onAttributeNameSelected: function(input, option) {
            input.onChange(option.value);
            this.props.changeField("channel.ebay.attributeImageMap", {});
            var attributeValues = [],
                attributeValue;
            for (var variationProduct of this.props.variationsDataForProduct) {
                if (!variationProduct.attributeValues[option.value]) {
                    continue;
                }
                attributeValue = variationProduct.attributeValues[option.value];
                attributeValues[attributeValue] = attributeValue;
            }
            this.setState({
                selectedAttributeName: option.value,
                selectedAttributeValues: Object.keys(attributeValues).map(key => attributeValues[key])
            });
        },
        findSelectedOption: function(value, options) {
            var selectedOption = {
                name: '',
                value: ''
            };
            options.forEach(function(option) {
                if (option.value == value) {
                    selectedOption = option;
                }
            });
            return selectedOption;
        },
        renderVariationImagePickers: function() {
            if (this.state.selectedAttributeValues.length == 0) {
                return null;
            }
            return this.state.selectedAttributeValues.map(attributeValue => {
                return <Field
                    name={"attributeImageMap." + attributeValue}
                    component={this.renderImagePickerField}
                    attributeValue={attributeValue}
                />
            });
        },
        renderImagePickerField: function(field) {
            return (<label className="input-container">
                <span className={"inputbox-label"}>{field.attributeValue}</span>
                {this.renderImagePicker(field)}
            </label>);
        },
        renderImagePicker: function (field) {
            if (this.props.product.images.length == 0) {
                return (
                    <p className="react-image-picker main-image-picker">No images available</p>
                );
            }
            return (
                <ImagePicker
                    className={"main-image-picker"}
                    name={field.input.name}
                    multiSelect={false}
                    images={this.props.product.images}
                    onImageSelected={this.onImageSelected.bind(this, field.input)}
                />
            );
        },
        onImageSelected: function(input, selectedImage, selectedImageIds) {
            input.onChange(selectedImageIds);
        },
        render: function() {
            return <span>
                <Field
                    name="imageAttributeName"
                    component={this.renderAttributeNameSelectComponent}
                    displayTitle={"Select images in your listing based on this attribute:"}
                    options={this.formatAttributeNamesOptions()}
                />
                {this.renderVariationImagePickers()}
            </span>
        }
    });

    const mapStateToProps = null;
    const mapDispatchToProps = function(dispatch) {
        return {
            changeField: function(fieldName, value) {
                dispatch(ReduxForm.change('createListing', fieldName, value));
            }
        };
    };

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(VariationImagePicker);
});
