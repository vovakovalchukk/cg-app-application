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
        const {countryCode, rowData} = this.props;
        this.props.actions.updateVat(rowData.id, countryCode, e.value);
    };

    generateOptionsFromVatRates = (vatRates) => {
        return Object.keys(vatRates).map(rate => {
            return {
                name: vatRates[rate].label,
                value: rate
            }
        });
    };
    selectToggle(productId) {
        this.props.actions.selectActiveToggle(this.props.columnKey, productId);
    };
    getVatSelectActive(product, containerElement) {
        return stateUtility.shouldShowSelect({
            product,
            select: this.props.select,
            columnKey: this.props.columnKey,
            containerElement,
            scroll: this.props.scroll,
            rows: this.props.rows
        })
    };
    render() {
        const {
            rowIndex,
            countryCode,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            rowData
        } = this.props;

        if (stateUtility.isVariation(rowData)) {
            return <span></span>
        }

        let productVat = this.props.vat.productsVat[countryCode].byProductId[rowData.id];

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

        let containerElement = this.props.cellNode;

        let portalSettingsParams = {
            elemType: elementTypes.SELECT_VAT_DROPDOWN,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds,
            containerElement
        };

        let portalSettingsForDropdown = portalSettingsFactory.createPortalSettings(portalSettingsParams);

        return (
            <div className={this.props.className}>
                <StatelessSelect
                    options={options}
                    selectedOption={selected}
                    onOptionChange={this.changeVat}
                    classNames={'u-width-140px'}
                    portalSettingsForDropdown={portalSettingsForDropdown}
                    selectToggle={this.selectToggle.bind(this, rowData.id, countryCode)}
                    active={this.getVatSelectActive(rowData, containerElement)}
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

