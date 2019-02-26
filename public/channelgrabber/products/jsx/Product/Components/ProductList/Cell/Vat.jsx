import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import StatelessSelect from 'Common/Components/Select--stateless';
import elementTypes from "../Portal/elementTypes";
import portalSettingsFactory from "../Portal/settingsFactory";

class VatCell extends React.Component {
    static defaultProps = {
        products: {},
        rowIndex: null,
        countryCode: '',
        width: '',
        distanceFromLeftSideOfTableToStartOfCell: '',
        actions: {},
        vat: {},
        scroll: {},
        rows: {}
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
    };
    selectToggle(productId, countryCode) {
        this.props.actions.toggleVatSelect(productId, countryCode);
    };
    getVatSelectActive(activePropOnState) {
        if (!activePropOnState || this.props.scroll.userScrolling || !this.props.rows.initialModifyHasOccurred) {
            return false;
        }
        return true;
    };
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

        if(!productVat){
            return (
                <span />
            )
        }

        let vatRatesForCountry = this.props.vat.vatRates[countryCode];
        let options = this.generateOptionsFromVatRates(vatRatesForCountry);

        let selectedVatKey = productVat.key;

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
            elemType: elementTypes.SELECT_VAT_DROPDOWN,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds
        });

        return (
            <div className={this.props.className}>
                <StatelessSelect
                    options={options}
                    selectedOption={selected}
                    onOptionChange={this.changeVat}
                    classNames={'u-width-140px'}
                    portalSettingsForDropdown={portalSettingsForDropdown}
                    selectToggle={this.selectToggle.bind(this, row.id, countryCode)}
                    active={this.getVatSelectActive(productVat.active)}
                    styleVars={{
                        widthOfInput: 110,
                        widthOfDropdown: 130
                    }}
                />
            </div>
        );
    }
}

export default VatCell;

