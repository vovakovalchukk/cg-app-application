import PropTypes from 'prop-types';
import React from 'react';
import ThenBySort from 'thenBy';
import Checkbox from 'Product/Components/Checkbox';
import Status from 'Product/Components/Status';
import VariationView from 'Product/Components/VariationView';
import Button from 'Common/Components/Button';
import Select from 'Common/Components/Select';
import Input from 'Common/Components/SafeInput';
import EditableFieldWithSubmit from 'Common/Components/EditableFieldWithSubmit';
import Tabs from 'Product/Components/SimpleTabs/Tabs';
import Pane from 'Product/Components/SimpleTabs/Pane';
import DimensionsView from 'Product/Components/DimensionsView';
import StockView from 'Product/Components/StockView';
import VatView from 'Product/Components/VatView';
import ListingsView from 'Product/Components/ListingsView';
import CreateListingIcon from 'Product/Components/CreateListingIcon';


class ProductRowComponent extends React.Component {
    static defaultProps = {
        product: [],
        variations: [],
        productLinks: {},
        maxVariationAttributes: 0,
        fetchingUpdatedStockLevelsForSkus: {},
        accounts: {},
        showVAT: true,
        massUnit: null,
        lengthUnit: null
    };

    state = {
        expanded: false,
        bulkStockMode: {
            name: '',
            value: ''
        },
        variations: this.props.variations,
        variationsSort: [
            {
                attribute: this.props.product.attributeNames[0],
                ascending: true
            }
        ]
    };

    componentWillReceiveProps(newProps) {
        if (newProps.variations.length === this.context.initialVariationCount) {
            this.setState({
                expanded: false// Reset expanded
            });
        }
        this.sortVariations(this.state.variationsSort, newProps.variations);
    }

    isParentProduct = () => {
        return this.props.product.variationCount !== undefined && this.props.product.variationCount >= 1
    };

    getProductVariationsView = () => {
        if (this.isParentProduct()) {
            return <VariationView
                parentProduct={this.props.product}
                onColumnSortClick={this.onColumnSortClick}
                variationsSort={this.state.variationsSort}
                attributeNames={this.props.product.attributeNames}
                variations={this.state.variations}
                productLinks={this.props.productLinks}
                maxVariationAttributes={this.props.maxVariationAttributes}
                fullView={this.state.expanded}
                linkedProductsEnabled={this.props.linkedProductsEnabled}
            />;
        } else {
            return <VariationView
                variations={[this.props.product]}
                fullView={this.state.expanded}
                linkedProductsEnabled={this.props.linkedProductsEnabled}
                productLinks={this.props.productLinks}
            />;
        }
    };

    getProductDetailsView = () => {
        var products = [this.props.product];
        if (this.isParentProduct()) {
            products = this.state.variations;
        }

        let panes = [];
        panes.push(this.getStockPane(products));
        panes.push(this.getDimensionsPane(products));
        if (this.props.showVAT) {
            panes.push(this.getVatPane(products));
        }
        panes.push(this.getListingsPane(products));

        return (
            <div className="details-layout-column">
                <Tabs selected={0}>
                    {panes}
                </Tabs>
            </div>
        );
    };

    getStockPane = (products) => {
        return (
            <Pane label="Stock">
                <StockView
                    variations={products}
                    fullView={this.state.expanded}
                    onVariationDetailChanged={this.onVariationDetailChanged}
                    fetchingUpdatedStockLevelsForSkus={this.props.fetchingUpdatedStockLevelsForSkus}
                />
            </Pane>
        );
    };

    getDimensionsPane = (products) => {
        return (
            <Pane label="Dimensions">
                <DimensionsView
                    variations={products}
                    fullView={this.state.expanded}
                    massUnit={this.props.massUnit}
                    lengthUnit={this.props.lengthUnit}
                />
            </Pane>
        );
    };

    getVatPane = (products) => {
        return (
            <Pane label="VAT">
                <VatView
                    parentProduct={this.props.product}
                    fullView={this.state.expanded}
                    onVatChanged={this.vatUpdated}
                    variationCount={this.state.variations.length}
                    adminCompanyUrl={this.props.adminCompanyUrl}
                />
            </Pane>
        );
    };

    getListingsPane = (products) => {
        return (
            <Pane label="Listings">
                <ListingsView accounts={this.props.product.accounts} listingsPerAccount={this.props.product.listingsPerAccount} variations={products} fullView={this.state.expanded} />
            </Pane>
        );
    };

    getExpandVariationsButton = () => {
        if (this.props.product.variationCount !== undefined && this.props.product.variationCount > this.context.initialVariationCount) {
            return <Button text={(this.state.expanded ? 'Contract' : 'Expand') + " Variations"} onClick={this.expandButtonClicked}/>
        }
    };

    getFooterActions = () => {
        if (this.isParentProduct()) {
            return this.getVariationsBulkActions();
        }
        return this.getStandaloneBulkActions();
    };

    getVariationsBulkActions = () => {
        return (
        <div className="footer-row">
            <div className="variations-layout-column">
                <div className="variations-button-holder">
                    {this.getExpandVariationsButton()}
                </div>
                <div className="stocklog-link-holder">
                    {this.context.isAdmin ? <a href={"/products/stockLog/"+this.props.product.id}>History Log</a> : ''}
                </div>
            </div>
            <div className="details-layout-column">
                <table>
                    <tbody>
                    <tr>
                        <td className="product-stock-available"></td>
                        <td className="product-stock-allocated"></td>
                        <td className="product-stock-available"></td>
                        <td colSpan="2" className="product-stock-mode">{this.getBulkStockModeDropdown()}</td>
                        <td colSpan="1" className="product-stock-level">{this.getBulkStockLevelInput()}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>
        );
    };

    getStandaloneBulkActions = () => {
        return (
        <div className="footer-row">
            <div className="variations-layout-column">
                <div className="stocklog-link-holder">
                    {this.context.isAdmin ? <a href={"/products/stockLog/"+this.props.product.id}>History Log</a> : ''}
                </div>
            </div>
        </div>
        );
    };

    getBulkStockModeDropdown = () => {
        if (this.state.variations.length > 0) {
            return <Select prefix="Set All" options={this.getStockModeOptions()} selectedOption={this.state.bulkStockMode} onOptionChange={this.bulkUpdateStockMode}/>
        }
    };

    getBulkStockLevelInput = () => {
        if (this.state.variations.length > 0) {
            return <Input name='bulk-level' submitCallback={this.bulkUpdateStockLevel} disabled={this.shouldBulkLevelBeDisabled()} />
        }
    };

    shouldBulkLevelBeDisabled = () => {
        var disabledStockMode = 'all';
        return (
            (this.state.bulkStockMode.value === "" || this.state.bulkStockMode.value === "null" || this.state.bulkStockMode.value === disabledStockMode) &&
            (this.props.product.stockModeDefault === disabledStockMode || this.props.product.stockModeDefault === null)
        );
    };

    vatUpdated = (taxRateId) => {
        n.notice('Updating product tax rate.');
        $.ajax({
            url : '/products/taxRate',
            data : { productId: this.props.product.id, taxRateId: taxRateId, memberState: taxRateId.substring(0, 2) },
            method : 'POST',
            dataType : 'json',
            success : function(response) {
                n.success('Product tax rate updated successfully.');
            },
            error : function(response) {
                n.showErrorNotification(response, "There was an error when attempting to update the product tax rate.");
            }
        });
    };

    expandButtonClicked = (e) => {
        this.setState({
            expanded: !this.state.expanded
        });

        if (this.state.variations.length <= this.context.initialVariationCount)  {
            window.triggerEvent('variationsRequest', {productId: this.props.product.id});
        }
    };

    onColumnSortClick = (attributeName) => {
        var newVariationSort = this.state.variationsSort.slice();

        if (newVariationSort.length < 1) {
            newVariationSort.push({attribute: attributeName, ascending: true});
        } else {
            var attributeAlreadyExists = false;
            newVariationSort.forEach(function (sort, index) {
                if (sort.attribute === attributeName) {
                    attributeAlreadyExists = true;
                    if (sort.ascending) {
                        newVariationSort[index].ascending = false;
                    } else {
                        newVariationSort.splice(index, 1);
                    }
                }
            });

            if (! attributeAlreadyExists) {
                newVariationSort.push({attribute: attributeName, ascending: true});
            }
        }
        this.setState({
            variationsSort: newVariationSort
        });
        this.sortVariations(newVariationSort);
    };

    sortVariations = (newVariationSort, variations) => {
        if (newVariationSort.length < 1) {
            return;
        }
        var newVariations = variations || this.state.variations.slice();
        var sortFunction = firstBy();
        newVariationSort.forEach(function (nextSort) {
            sortFunction = sortFunction.thenBy(function(v){
                return v.attributeValues[nextSort.attribute] ? v.attributeValues[nextSort.attribute] : "";
            }, {ignoreCase: true, direction: (nextSort.ascending ? 1 : -1)});
        });

        sortFunction = sortFunction.thenBy(function(v){
            return v.sku ? v.sku : "";
        }, {ignoreCase: true, direction: 1});

        this.setState({
            variations: newVariations.sort(sortFunction)
        });
    };

    getStockModeOptions = () => {
        if (this.state.variations.length < 1) {
            return [];
        }
        var options = [];
        this.state.variations[0].stockModeOptions.map(function(option) {
            options.push({value: option.value, name: option.title});
        });
        return options;
    };

    getStockModeLevel = () => {
        if (this.state.variations.length < 1) {
            return;
        }
        return this.state.variations[0].stock.stockLevel;
    };

    bulkUpdateStockLevel = (name, value) => {
        if (this.state.variations.length < 1) {
            return;
        }
        n.notice('Bulk updating stock level for all variations.');
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: 'products/stockLevel',
                type: 'POST',
                dataType : 'json',
                data: {
                    id: this.props.product.id,
                    stockLevel: value
                },
                success: function(response) {
                    n.success('Bulk stock level updated successfully.');
                    resolve({ savedValue: response.level || 0 });
                    this.updateVariationsStockMode(response);
                }.bind(this),
                error: function(error) {
                    n.showErrorNotification(error, "There was an error when attempting to bulk update the stock level.");
                    reject(new Error(error));
                }
            });
        }.bind(this));
    };

    bulkUpdateStockMode = (stockMode) => {
        n.notice('Bulk updating stock mode for all variations.');
        $.ajax({
            url : '/products/stockMode',
            data : { id: this.props.product.id, stockMode: stockMode.value },
            method : 'POST',
            dataType : 'json',
            success : function(response) {
                n.success('Bulk stock mode updated successfully.');
                this.updateVariationsStockMode(response);
                this.setState({
                    bulkStockMode: stockMode
                });
            }.bind(this),
            error : function(response) {
                n.showErrorNotification(response, "There was an error when attempting to bulk update the stock mode.");
            }
        });
    };

    updateVariationsStockMode = (stockModes) => {
        var updatedVariations = this.state.variations.slice();
        updatedVariations.forEach(function(variation) {
            var stockMode = stockModes[variation.sku];
            var stockModeOption;
            variation.stockModeOptions.forEach(function (mode, stockModeIndex) {
                variation.stockModeOptions[stockModeIndex].selected = (mode.value == stockMode.mode + "");
                if (variation.stockModeOptions[stockModeIndex].selected) {
                    stockModeOption = mode;
                }
            });
            variation.stockModeDesc = stockModeOption.title;
            variation.stock.stockMode = stockMode.mode;
            variation.stock.stockLevel = stockMode.level || 0;
            return variation;
        });
        this.setState({
            variations: updatedVariations
        });
    };

    updateProductName = (newName) => {
        n.notice('Updating the product name.');
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: 'products/name',
                type: 'POST',
                dataType : 'json',
                data : { id: this.props.product.id, name: newName },
                success: function(response) {
                    n.success('Product name updated successfully.');
                    resolve({ newFieldText: newName });
                }.bind(this),
                error: function(error) {
                    n.showErrorNotification(error, "There was an error when attempting to update the product name.");
                    reject(new Error(error));
                }
            });
        }.bind(this));
    };

    onVariationDetailChanged = (updatedVariation) => {
        this.triggerProductRefresh(updatedVariation);
        if (this.props.product.variationCount <= 1) {
            this.setState({
                variations: [updatedVariation]
            });
            return;
        }
        var updatedVariations = this.state.variations.slice();

        updatedVariations.forEach(function (variation) {
            if (updatedVariation.sku === variation.sku) {
                return updatedVariation;
            }
        });

        this.setState({
            variations: updatedVariations
        });
    };

    triggerProductRefresh = (updatedVariation) => {
        window.triggerEvent('productRefresh', {product: updatedVariation});
    };

    render() {
        return (
            <div className="product-container" id={"product-container-" + this.props.product.id}>
                <input type="hidden" value={this.props.product.id} name="id" />
                <input type="hidden" value={this.props.product.eTag} name={"product[" + this.props.product.id + "][eTag]"} />
                <div className="product-info-container">
                    <div className="product-header">
                        <div className="checkbox-and-title">
                            <Checkbox id={this.props.product.id} />
                            <EditableFieldWithSubmit initialFieldText={this.props.product.name} onSubmit={this.updateProductName} />
                        </div>
                        <td>
                            <CreateListingIcon
                                isSimpleProduct={!! (this.props.variations.length == 0)}
                                accountsAvailable={this.props.accounts}
                                productId={this.props.product.id}
                                onCreateListingIconClick={this.props.onCreateListingIconClick}
                                availableChannels={this.props.createListingsAllowedChannels}
                                availableVariationsChannels={this.props.createListingsAllowedVariationChannels}
                            />
                        </td>
                    </div>
                    <div className={"product-content-container" + (this.state.expanded ? "" : " contracted")}>
                        <div className="variations-layout-column">
                            {this.getProductVariationsView()}
                        </div>
                        {this.getProductDetailsView()}
                    </div>
                    <div className="product-footer">
                        {this.getFooterActions()}
                    </div>
                </div>
            </div>
        );
    }
}

ProductRowComponent.contextTypes = {
    imageUtils: PropTypes.object,
    isAdmin: PropTypes.bool,
    initialVariationCount: PropTypes.number
};

export default ProductRowComponent;

