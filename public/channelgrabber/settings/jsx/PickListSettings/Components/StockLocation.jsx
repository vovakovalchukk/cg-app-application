import React from 'react';
import Row from './Row';

class StockLocation extends React.Component {
    defaultProps = {
        name: null,
        onChange: null
    };

    constructor(props) {
        super(props);
        this.active = React.createRef();
        this.state = {locationNames: this.props.locationNames || []};
        this.placeholders = ["Warehouse", "Aisle", "Shelf"];
        if (this.state.locationNames.length === 0) {
            this.addLocationName("");
        }
    }

    componentDidUpdate() {
        if (this.state.locationNames.length === 0) {
            this.addLocationName("");
        }

        let active = this.active.current;
        if (active) {
            active.focus();
        }
    }

    addLocationName(locationName) {
        let locationNames = this.state.locationNames;
        locationNames.push(locationName);
        this.setState(
            {locationNames: locationNames},
            () => {
                if (typeof this.props.onChange === "function") {
                    this.props.onChange(this.locationNames());
                }
            }
        );
    }

    setLocationName(locationName, index) {
        let locationNames = this.state.locationNames;
        locationNames[index] = locationName;
        this.setState(
            {locationNames: locationNames},
            () => {
                if (typeof this.props.onChange === "function") {
                    this.props.onChange(this.locationNames());
                }
            }
        );
    }

    removeLocationName(index) {
        let locationNames = this.state.locationNames;
        locationNames.splice(index, 1);
        this.setState(
            {locationNames: locationNames},
            () => {
                if (typeof this.props.onChange === "function") {
                    this.props.onChange(this.locationNames());
                }
            }
        );
    }

    locationNames() {
        return this.state.locationNames;
    }

    render() {
        return (
            <div className="stock-location">
                {this.locationNames().map((locationName, index) => this.renderStockLocation(locationName, index))}
                <div className="align-right">
                    <div
                        className="sprite sprite-plus-18-black"
                        onClick={() => this.addLocationName("")}
                    />
                </div>
            </div>
        );
    }

    renderStockLocation(locationName, index) {
        return (
            <Row label={"Stock Location " + (index + 1) + ":"}>
                <div className="stock-location">
                    <input
                        ref={this.active}
                        name={this.props.name + "[" + index + "]"}
                        className="inputbox"
                        onChange={(event) => this.setLocationName(event.target.value, index)}
                        value={locationName}
                        placeholder={this.placeholders[index] || null}
                    />
                </div>
                <div
                    className="sprite sprite-minus-18-black"
                    onClick={() => this.removeLocationName(index)}
                />
            </Row>
        );
    }
}

export default StockLocation;