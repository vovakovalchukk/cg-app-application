import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import Select from 'Common/Components/Select';
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

    render() {
        const {
            products,
            rowIndex,
            countryCode,
            distanceFromLeftSideOfTableToStartOfCell,
            width
        } = this.props;
        const row = stateUtility.getRowData(products, rowIndex);

        console.log('VAT: ', this.props.vat);

        if (stateUtility.isVariation(row)) {
            return <span></span>
        }

        let productVat = this.props.vat.productsVat[row.id];

        console.log('before failign.. ', {
            'this.props.vat.productsVat[row.id]':this.props.vat.productsVat[row.id],
            'row.id': row.id,
            'this.props.vat.productsVat': this.props.vat.productsVat,
            'this.props.vat.vatRates':this.props.vat.vatRates,
            countryCode
        });




        let vatRatesForCountry = this.props.vat.vatRates[countryCode];

        let options = this.generateOptionsFromVatRates(vatRatesForCountry);
        console.log('got options: ', options);

        let selectedVatKey = productVat[countryCode];

        let selectedVat = options.find(option => (selectedVatKey === option.value));
        if (!selectedVat) {
            return <span>no selected vat...</span>
        }

        let selectedLabel = selectedVat.name;
        let selected = {
            name: selectedLabel,
            value: selectedVatKey
        };

        console.log('VAT : ', {
            'row.id': row.id,
            row,
            'this.props.vat.vatRates[countryCode]': this.props.vat.vatRates[countryCode],
            countryCode,
            'this.props': this.props,
            rowIndex,
            selected
        });

        let portalSettingsForDropdown = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.SELECT_DROPDOWN,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds
        });

        return (
            <div className={this.props.className}>
                <Select
                    options={options}
                    selectedOption={selected}
                    onOptionChange={this.changeVat}
                    classNames={'u-width-140px'}
                    portalSettingsForDropdown={portalSettingsForDropdown}
                />
            </div>
        );
    }
}

export default VatCell;

