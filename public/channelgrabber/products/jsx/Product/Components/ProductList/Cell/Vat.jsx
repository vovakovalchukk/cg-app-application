import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import StatelessSelect from 'Common/Components/Select--stateless';
import elementTypes from "../Portal/elementTypes";
import portalSettingsFactory from "../Portal/settingsFactory";

class VatCell extends React.Component {
    static defaultProps = {
        products: {},
        rowIndex: null
    };

    state = {};

    changeVat = (e) => {
        const {products, rowIndex, countryCode} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);
        this.props.actions.updateVat(row.id, countryCode, e.value);
    };

    generateOptionsFromVatRates = (vatRates) => {
        return Object.keys(vatRates).map(rate => {
            return {
                name: vatRates[rate].label,
                value: rate
            }
        });
    }
    selectToggle(row, productId) {
        this.props.actions.toggleVatSelect(productId, row);
    }
    render() {
        const {
            products,
            rowIndex,
            countryCode,
            distanceFromLeftSideOfTableToStartOfCell,
            width
        } = this.props;
        const row = stateUtility.getRowData(products, rowIndex);

        if (stateUtility.isVariation(row)) {
            return <span></span>
        }

        let productVat = this.props.vat.productsVat[countryCode].byProductId[row.id];

        let vatRatesForCountry = this.props.vat.vatRates[countryCode];
        let options = this.generateOptionsFromVatRates(vatRatesForCountry);

        let selectedVatKey = productVat.key;
        debugger;

        let selectedVat = options.find(option => (selectedVatKey === option.value));
        if (!selectedVat) {
            return <span></span>
        }

        let selectedLabel = selectedVat.name;
        let selected = {
            name: selectedLabel,
            value: selectedVatKey
        };
        let portalSettingsForDropdown = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.SELECT_DROPDOWN,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds
        });
        ////
        return (
            <div className={this.props.className}>
                <StatelessSelect
                    options={options}
                    selectedOption={selected}
                    onOptionChange={this.changeVat}
                    classNames={'u-width-140px'}
                    portalSettingsForDropdown={portalSettingsForDropdown}
                    selectToggle={this.selectToggle.bind(this, row)}
                />
            </div>
        );
    }
}

export default VatCell;

