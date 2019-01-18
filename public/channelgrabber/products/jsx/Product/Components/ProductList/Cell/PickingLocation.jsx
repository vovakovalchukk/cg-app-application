import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import StatelessSelectComponent from 'Product/Components/ProductList/Components/Select--stateless';
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";

class PickingLocationCell extends React.Component {
    static defaultProps = {
        products: {},
        rowIndex: null,
        pickLocations: null
    };

    render() {
        const {products, rowIndex, pickLocations} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);

        if (!stateUtility.isSimpleProduct(row) && !stateUtility.isVariation(row)) {
            return (
                <span className={this.props.className} />
            );
        }

        return (
            <span className={this.props.className}>
                {pickLocations.names.map((name, index) => this.renderPickLocation(name, index, row, rowIndex))}
            </span>
        );
    }

    renderPickLocation(name, index, row, rowIndex) {
        const {distanceFromLeftSideOfTableToStartOfCell, pickLocations, padding, selectWidth} = this.props;

        let portalSettingsForDropdown = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.SELECT_DROPDOWN,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell: distanceFromLeftSideOfTableToStartOfCell + padding + (selectWidth * index),
            width: selectWidth,
            allRows: this.props.rows.allIds
        });

        let selected = null;
        if (pickLocations.byProductId.hasOwnProperty(row.id) && pickLocations.byProductId[row.id].hasOwnProperty(index)) {
            selected = pickLocations.byProductId[row.id][index];
        } else if (row.pickingLocations.hasOwnProperty(index)) {
            selected = row.pickingLocations[index];
        }

        let select = React.createRef();
        return (
            <StatelessSelectComponent
                ref={select}
                title={name}
                prefix={name}
                active={
                    pickLocations.selected
                    && pickLocations.selected.productId === row.id
                    && pickLocations.selected.level === index
                }
                options={(pickLocations.values[index] || []).map((value) => {
                    return {name: value, value};
                })}
                selectedOption={((pickingLocation) => {
                    return pickingLocation ? {name: pickingLocation, value: pickingLocation} : null;
                })(selected)}
                selectToggle={() => {
                    this.props.actions.togglePickLocationsSelect(row.id, index);
                    select.current.setFilter("");
                }}
                onOptionChange={(selectedOption) => {
                    let exactMatch = (pickLocations.values[index] || []).find((pickLocation) => {
                        return pickLocation.toLowerCase() === selectedOption.value.toLowerCase();
                    });
                    this.props.actions.selectPickLocation(row.id, index, exactMatch || selectedOption.value);
                }}
                portalSettingsForDropdown={portalSettingsForDropdown}
                styleVars={{
                    widthOfDropdown: selectWidth - 1,
                    widthOfInput: selectWidth - 22
                }}
            >
                {this.renderPickLocationCustomOption(select)}
            </StatelessSelectComponent>
        );
    }

    renderPickLocationCustomOption(select) {
        let onKeyUp = (event) => {
            let value = event.target.value.trim();
            if (event.keyCode === 13 && value.length > 0) {
                select.current.onOptionSelected(value);
                select.current.onComponentClick();
            } else {
                select.current.setFilter(value);
            }
        };

        return (
            <div className={"search-selected-wrapper clearfix"} onClick={(event) => event.stopPropagation()}>
                <input className={"search-selected"} onKeyUp={onKeyUp} />
            </div>
        );
    }
}

export default PickingLocationCell;