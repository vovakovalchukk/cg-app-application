import React from 'react';
import ReduxForm from 'redux-form';
import ImageDropDown from 'Product/Components/CreateListing/Form/Shared/ImageDropDown';


const Field = ReduxForm.Field;

class VariationTableComponent extends React.Component {
    static defaultProps = {
        variationsDataForProduct: [],
        product: {},
        showImages: true,
        renderImagePicker: true,
        renderStaticImageFromFormValues: false,
        attributeNames: [],
        attributeNameMap: {},
        sectionName: '',
        imageDropdownsDisabled: false,
        shouldRenderStaticImagesFromVariationValues: false,
        containerCssClasses:'',
        tableCssClasses:'',
        renderCustomTableHeaders: function() { return null; },
        renderCustomTableRows: function() { return null; },
        variationImages: {}
    };

    renderImageHeader = () => {
        if (!this.props.showImages) {
            return;
        }
        return <th>Image</th>;
    };

    renderAttributeHeaders = () => {
        return this.props.attributeNames.map(function(attributeName) {
            var attributeNameText = this.props.attributeNameMap[attributeName] ? this.props.attributeNameMap[attributeName] : attributeName;
            return <th
                className="attribute-header with-title"
                title={attributeNameText}
            >
                {attributeNameText}
            </th>;
        }.bind(this));
    };

    renderVariationRows = () => {
        return this.props.variationsDataForProduct.map(function(variation) {
            return <tr>
                <td>{this.renderImageColumn(variation)}</td>
                <td>{variation.sku}</td>
                {this.renderAttributeColumns(variation)}
                {this.props.renderCustomTableRows(variation)}
            </tr>
        }.bind(this));
    };

    renderImageColumn = (variation) => {
        if (!this.props.showImages) {
            return '';
        }
        if (this.props.product.images == 0) {
            return 'No images available';
        }

        if (!this.props.renderImagePicker) {
            return this.renderStaticImage(variation);
        }

        return <Field
            name={"images." + variation.sku + ".imageId"}
            component={this.renderImageField}
            variation={variation}
        />;
    };

    renderStaticImage = (variation) => {
        if (this.props.renderStaticImageFromFormValues) {
            return <Field
                name={"images." + variation.sku + ".imageId"}
                component={this.renderStaticImageFromFormValues}
                variation={variation}
            />;
        }

        const selectedImageForVariation = this.props.variationImages[variation.sku];
        if (!selectedImageForVariation) {
            return '';
        }

        const image = this.findSelectedImageForVariation(selectedImageForVariation.imageId);
        return this.renderStaticImageComponent(image.url);
    };

    renderStaticImageComponent = (imageUrl) => {
        return <div className="image-dropdown-target">
            <div className="react-image-picker">
                <span className="react-image-picker-image">
                    <img src={imageUrl}/>
                </span>
            </div>
        </div>;
    };

    renderImageField = (field) => {
        const selected = (field.variation.images.length > 0 ? field.variation.images[0] : this.props.product.images[0]);
        return <ImageDropDown
            selected={selected}
            autoSelectFirst={false}
            images={this.props.product.images}
            onChange={this.onImageSelected.bind(this, field)}
            dropdownDisabled={this.props.imageDropdownsDisabled}
        />
    };

    onImageSelected = (field, image) => {
        this.onInputChange(field.input, image.target.value);
    };

    getStaticImage = (fieldValue, fieldVariation) => {
        if (this.props.shouldRenderStaticImagesFromVariationValues && fieldVariation.images) {
            return fieldVariation.images[0];
        }
        return this.findSelectedImageForVariation(fieldValue);
    };

    renderStaticImageFromFormValues = (field) => {
        let image = this.getStaticImage(field.input.value, field.variation);
        return (
            <div className="image-dropdown-target">
                <div className="react-image-picker">
                    <span className="react-image-picker-image">
                        <img src={image.url}/>
                    </span>
                </div>
            </div>
        );
    };

    findSelectedImageForVariation = (imageId) => {
        var selectedImage = {url: ""};
        if (!imageId) {
            return selectedImage;
        }
        var foundImage = this.props.product.images.find(image => image.id == imageId);
        return foundImage ? foundImage : selectedImage;
    };

    renderAttributeColumns = (variation) => {
        return this.props.attributeNames.map(function(attributeName) {
            return <td>{variation.attributeValues[attributeName]}</td>
        });
    };

    onInputChange = (input, value) => {
        input.onChange(value);
    };

    render() {
        return (
            <div className={"variation-picker " + this.props.containerCssClasses}>
                <table className={this.props.tableCssClasses}>
                    <thead>
                    <tr>
                        {this.renderImageHeader()}
                        <th>SKU</th>
                        {this.renderAttributeHeaders()}
                        {this.props.renderCustomTableHeaders()}
                    </tr>
                    </thead>
                    {this.renderVariationRows()}
                </table>
            </div>
        );
    }
}

export default VariationTableComponent;

