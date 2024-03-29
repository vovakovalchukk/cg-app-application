import React from 'react';
import {connect} from 'react-redux';
import {Field, change as reduxFormChange} from 'redux-form';
import Select from 'Common/Components/Select';
import ImagePicker from 'Common/Components/ImagePicker';
import Validators from '../../../Validators';

class VariationImagePicker extends React.Component {
    static defaultProps = {
        product: {},
        variationsDataForProduct: {},
        attributeNames: {}
    };

    state = {
        selectedAttributeName: null,
        selectedAttributeValues: [],
        attributeNameSelectField: null
    };

    formatAttributeNamesOptions = () => {
        return this.props.product.attributeNames.map(name => {
            return {
                name: name,
                value: name
            }
        });
    };

    renderAttributeNameSelectComponent = (field) => {
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
    };

    onAttributeNameSelected = (input, option) => {
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
    };

    findSelectedOption = (value, options) => {
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
    };

    renderVariationImagePickers = () => {
        return this.state.selectedAttributeValues.length > 0 ?
            this.state.selectedAttributeValues.map(attributeValue => {
                return <Field
                    name={"attributeImageMap." + attributeValue}
                    component={this.renderImagePickerField}
                    attributeValue={attributeValue}
                    validate={Validators.required}
                />
            })
            : null;
    };

    renderImagePickerField = (field) => {
        return (<label className="input-container">
            <span className={"inputbox-label"}>{field.attributeValue}</span>
            {this.renderImagePicker(field)}
            {Validators.shouldShowError(field) && (
                <span className="input-error">{field.meta.error}</span>
            )}
        </label>);
    };

    renderImagePicker = (field) => {
        if (this.props.product.images.length == 0) {
            return (
                <p className="react-image-picker main-image-picker">No images available</p>
            );
        }
        return (
            <ImagePicker
                className={this.getImagePickerClassName(field)}
                name={field.input.name}
                multiSelect={false}
                images={this.props.product.images}
                onImageSelected={this.onImageSelected.bind(this, field.input)}
            />
        );
    };

    getImagePickerClassName = (field) => {
        return "main-image-picker main-image-picker" + (Validators.shouldShowError(field) ? ' error' : null);
    };

    onImageSelected = (input, selectedImage, selectedImageIds) => {
        input.onChange(selectedImageIds);
    };

    render() {
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
}

const mapStateToProps = null;
const mapDispatchToProps = function(dispatch) {
    return {
        changeField: function(fieldName, value) {
            dispatch(reduxFormChange('createListing', fieldName, value));
        }
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(VariationImagePicker);

