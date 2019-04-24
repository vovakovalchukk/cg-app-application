import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import StatelessSelectComponent from 'Common/Components/Select--stateless';
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";

class PickingLocationCell extends React.Component {
    static defaultProps = {
        products: {},
        rowIndex: null,
        pickLocations: null,
        selectWidth: null,
        rowData: [],
        distanceFromLeftSideOfTableToStartOfCell: null,
        padding: null
    };

    render() {
        const {rowIndex, pickLocations} = this.props;
        const row = this.props.rowData;

        if (!stateUtility.isSimpleProduct(row) && !stateUtility.isVariation(row)) {
            return (
                <span className={this.props.className}/>
            );
        }

        return (
            <span className={this.props.className}>
                {pickLocations.names.map((name, index) => this.renderPickLocation(name, index, row, rowIndex))}
            </span>
        );
    }
    getPickLocationActive(pickLocations, row, index) {
        let isActive = stateUtility.isCurrentActiveSelect(row, this.props.select, this.props.columnKey, index);

        if (!isActive ||
            this.props.scroll.userScrolling ||
            !this.props.rows.initialModifyHasOccurred
        ) {
            return false;
        }

        return true;
    };
    renderPickLocation(name, index, row) {
        const {pickLocations, selectWidth} = this.props;
        let portalSettingsForDropdown = this.getPortalSettings(index);

        let selected = null;
        if (pickLocations.byProductId.hasOwnProperty(row.id) && pickLocations.byProductId[row.id].hasOwnProperty(index)) {
            selected = pickLocations.byProductId[row.id][index];
        } else
            if (row.pickingLocations.hasOwnProperty(index)) {
                selected = row.pickingLocations[index];
            }

        let select = React.createRef();

        return (
            <StatelessSelectComponent
                ref={select}
                title={name}
                prefix={name}
                active={this.getPickLocationActive(pickLocations, row, index)}
                options={(pickLocations.values[index] || []).map((value) => {
                    return {name: value, value};
                })}
                selectedOption={((pickingLocation) => {
                    return pickingLocation ? {name: pickingLocation, value: pickingLocation} : null;
                })(selected)}
                selectToggle={() => {
                    this.props.actions.selectActiveToggle(this.props.columnKey, row.id, index);
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
    getPortalSettings(index) {
        const {distanceFromLeftSideOfTableToStartOfCell, padding, selectWidth, rowIndex} = this.props;

        let containerElement = this.props.cellNode;

        let portalSettingsForDropdown = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.SELECT_DROPDOWN,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell: distanceFromLeftSideOfTableToStartOfCell + padding + (selectWidth * index),
            width: selectWidth,
            allRows: this.props.rows.allIds,
            containerElement
        });

        return portalSettingsForDropdown;
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
                <input className={"search-selected"} onKeyUp={onKeyUp}/>
            </div>
        );
    }
}

export default PickingLocationCell;