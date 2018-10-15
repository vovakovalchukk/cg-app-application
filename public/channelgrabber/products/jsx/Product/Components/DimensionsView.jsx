import React from 'react';
import DimensionsRow from 'Product/Components/DimensionsRow';


class DimensionsViewComponent extends React.Component {
    static defaultProps = {
        variations: [],
        fullView: false,
        massUnit: null,
        lengthUnit: null
    };

    getHeaders = () => {
        return [
            <th key="weight">Weight ({this.props.massUnit})</th>,
            <th key="height">Height ({this.props.lengthUnit})</th>,
            <th key="width">Width ({this.props.lengthUnit})</th>,
            <th key="length">Length ({this.props.lengthUnit})</th>,
        ];
    };

    dimensionUpdated = (e) => {
        var sku = e.type.substring('dimension-'.length);
        var newValue = e.detail.value;
        var dimension = e.detail.dimension;
        var updatedVariation = null;

        this.props.variations.forEach(function (variation) {
            if (variation.sku === sku) {
                updatedVariation = variation;
            }
        });
        updatedVariation.details[dimension] = newValue;
    };

    render() {
        var count = 0;
        return (
            <div className="details-table">
                <table>
                    <thead>
                    <tr>
                        {this.getHeaders()}
                    </tr>
                    </thead>
                    <tbody>
                    {this.props.variations.map(function (variation) {
                        if ((! this.props.fullView) && count > 1) {
                            return;
                        }
                        count++;
                        return <DimensionsRow key={variation.id} variation={variation} dimensionUpdated={this.dimensionUpdated}/>;
                    }.bind(this))}
                    </tbody>
                </table>
            </div>
        );
    }
}

export default DimensionsViewComponent;
