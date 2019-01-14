import React from 'react';
import Row from './Row';
import Checkbox from './Checkbox';
import Select from './Select';
import StockLocation from './StockLocation';
import ButtonComponent from "Common/Components/Button";
import ajaxRequester from "AjaxRequester";

class RootComponent extends React.Component {
    defaultProps = {
        saveUrl: null
    };

    constructor(props) {
        super(props);
        this.state = Object.assign({}, {eTag: (props.eTag || null)}, (props.pickList || {}));
    }

    submit() {
        ajaxRequester.sendRequest(this.props.saveUrl, this.state, (response) => {
            ajaxRequester.handleSuccess(response);
            if ("eTag" in response) {
                this.setState({eTag: response.eTag});
            }
        });
    }

    sortFields() {
        let sortFields = this.props.sortFields || {};
        let sortFieldsMap = this.props.sortFieldsMap || {};
        return Object.keys(sortFields).map((sortField) => {
            let option = {name: sortFields[sortField], value: sortField};
            if (sortField in sortFieldsMap) {
                option["disabled"] = !(this.state[sortFieldsMap[sortField]] || false);
            }
            return option;
        });
    }

    sortDirections() {
        let sortDirections = this.props.sortDirections || {};
        return Object.keys(sortDirections).map((sortDirection) => {
            return {name: sortDirections[sortDirection], value: sortDirection};
        });
    }

    render() {
        return (
            <div className="pick-list-form">
                <Row label="Show Pictures:">
                    <Checkbox
                        name="showPictures"
                        onChange={(checked) => this.setState({showPictures: checked})}
                        checked={this.state.showPictures || false}
                    />
                </Row>
                <Row label="Include SKUless Products:">
                    <Checkbox
                        name="showSkuless"
                        onChange={(checked) => this.setState({showSkuless: checked})}
                        checked={this.state.showSkuless || false}
                    />
                </Row>
                <Row label="Show Picking Location:">
                    <Checkbox
                        name="showPickingLocations"
                        onChange={(checked) => this.setState({showPickingLocations: checked})}
                        checked={this.state.showPickingLocations || false}
                    />
                </Row>
                <Row label="Sort by Column:" className="selects">
                    <Select
                        name="sortField"
                        onChange={(selected) => this.setState({sortField: selected})}
                        selected={this.state.sortField || null}
                        options={this.sortFields()}
                    />
                    <Select
                        name="sortDirection"
                        onChange={(selected) => this.setState({sortDirection: selected})}
                        selected={this.state.sortDirection || null}
                        options={this.sortDirections()}
                    />
                </Row>
                <StockLocation
                    name="locationNames"
                    onChange={(locationNames) => this.setState({locationNames: locationNames})}
                    locationNames={this.state.locationNames || []}
                />
                <div className="align-center">
                    <ButtonComponent
                        text="Save"
                        onClick={() => this.submit()}
                    />
                </div>
            </div>
        );
    }
}

export default RootComponent;