import React from 'react';
import VariationTable from '../Components/CreateListing/VariationTable';

class AssignedProductsTable extends React.Component {
    static defaultProps = {
        selectedProducts: {},
        defaultProductImage: ''
    };

    renderTableHeader = () => {
        return [
            <th>{"Selected Product Title"}</th>,
            <th>{"Selected Product Image"}</th>,
            <th>{}</th>
        ];
    };

    renderTableColumns = (variation) => {
        let product = this.findProductForSku(variation.sku);
        return [
            <td>{product ? product.title : '-'}</td>,
            <td>{product ? this.renderImage(product) : '-'}</td>,
            <td>
                {product ? this.renderClearButton(variation.sku) : ''}
            </td>
        ];
    };

    renderClearButton = (sku) => {
        return <span className="remove-icon">
            <i
                className='fa fa-2x fa-minus-square icon-create-listing'
                aria-hidden='true'
                onClick={this.props.clearSelectedProduct.bind(this, sku)}
            />
        </span>;
    };

    findProductForSku = (sku) => {
        return this.props.selectedProducts[sku] ? this.props.selectedProducts[sku] : null;
    };

    renderImage = (product) => {
        return (
            <div className="image-dropdown-target">
                <div className="react-image-picker">
                    <span className="react-image-picker-image">
                        <img src={product.imageUrl ? product.imageUrl : this.props.defaultProductImage}/>
                    </span>
                </div>
            </div>
        );
    };

    render() {
        return <VariationTable
            sectionName={"assigned-products"}
            variationsDataForProduct={this.props.variationsDataForProduct}
            product={this.props.product}
            showImages={true}
            renderImagePicker={false}
            attributeNames={this.props.attributeNames}
            attributeNameMap={this.props.attributeNameMap}
            renderCustomTableHeaders={this.renderTableHeader}
            renderCustomTableRows={this.renderTableColumns}
            variationImages={this.props.variationImages}
            containerCssClasses={"assigned-products-table"}
        />;
    }
}

export default AssignedProductsTable;

