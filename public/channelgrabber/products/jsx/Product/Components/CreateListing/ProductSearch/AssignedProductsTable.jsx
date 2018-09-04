define([
    'react',
    '../Components/CreateListing/VariationTable'
], function (
    React,
    VariationTable
) {
    const AssignedProductsTable = React.createClass({
        getDefaultProps: function() {
            return {
                selectedProducts: {}
            }
        },
        renderTableHeader: function () {
            return [
                <th>{"Selected Product Title"}</th>,
                <th>{"Selected Product Image"}</th>,
                <th>{}</th>
            ];
        },
        renderTableColumns: function (variation) {
            let product = this.findProductForSku(variation.sku);
            return [
                <td>{product ? product.title : '-'}</td>,
                <td>{product ? this.renderImage(product) : '-'}</td>,
                <td>
                    {product ? <button onClick={this.props.clearSelectedProduct.bind(this, variation.sku)}>Clear</button> : ''}
                </td>
            ];
        },
        findProductForSku: function(sku) {
            return this.props.selectedProducts[sku] ? this.props.selectedProducts[sku] : null;
        },
        renderImage: function(product) {
            if (!product.imageUrl) {
                return null;
            }

            return (
                <div className="image-dropdown-target">
                    <div className="react-image-picker">
                        <span className="react-image-picker-image">
                            <img src={product.imageUrl}/>
                        </span>
                    </div>
                </div>
            );
        },
        render: function() {
            if (Object.keys(this.props.selectedProducts).length === 0) {
                return null;
            }

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
            />;
        }
    });

    return AssignedProductsTable;
});
