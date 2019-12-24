import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import StatelessSelect from 'Common/Components/Select--stateless';
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";

class SupplierCell extends React.Component {
    static defaultProps = {
        rowIndex: '',
        distanceFromLeftSideOfTableToStartOfCell: '',
        width: '',
        actions: {},
        rows: {},
        cellNode: null
    };
    getSelectActive(product, containerElement) {
        return stateUtility.shouldShowSelect({
            product,
            select: this.props.select,
            columnKey: this.props.columnKey,
            containerElement,
            scroll: this.props.scroll,
            rows: this.props.rows
        });
    };
    selectToggle(productId) {
        this.props.actions.selectActiveToggle(this.props.columnKey, productId);
    };
    getSelectedOption(product) {
        const supplier = this.props.supplier.byProductId[product.id];
        if (!supplier) {
            return null;
        }
        return this.props.supplier.options.find((option) => {
            return option.value == supplier;
        });
    };
    render() {
        let {
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            rows,
            rowData
        } = this.props;

        if (rowData.id === 1) {
            console.log(this.props);
        }

        if (stateUtility.isVariation(rowData)) {
            return <span/>;
        }

        let containerElement = this.props.cellNode;

        let portalSettingsParams = {
            elemType: elementTypes.SUPPLIER,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds,
            containerElement
        };

        let portalSettingsForDropdown = portalSettingsFactory.createPortalSettings(portalSettingsParams);

        return (
            <div className={this.props.className + " supplier"}>
                <StatelessSelect
                    options={this.props.supplier.options}
                    selectedOption={this.getSelectedOption(rowData)}
                    onOptionChange={(option) => {
                        this.props.actions.updateSupplier(rowData.id, option.value);
                    }}
                    classNames={'u-width-140px'}
                    portalSettingsForDropdown={portalSettingsForDropdown}
                    selectToggle={this.selectToggle.bind(this, rowData.id)}
                    active={this.getSelectActive(rowData, containerElement)}
                    styleVars={{
                        widthOfInput: 110,
                        widthOfDropdown: 130
                    }}
                />
            </div>
        );
    }
}

export default SupplierCell;