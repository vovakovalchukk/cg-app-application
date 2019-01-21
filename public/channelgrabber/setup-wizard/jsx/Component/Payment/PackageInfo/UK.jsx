import React from 'react';
import BillingPeriod from 'SetupWizard/Component/Payment/BillingPeriod';


class PackageInfoComponent extends React.Component {
    static defaultProps = {
        id: null,
        name: null,
        band: null,
        monthlyPrice: null,
        price: null,
        orderVolume: null,
        billingDuration: null,
        billingDurationChangeAllowed: true,
        billingDurationChanged: null
    };

    static getDerivedStateFromProps(props) {
        return {
            billingDuration: props.billingDuration
        };
    };

    billingDurationChanged = (billingDuration) => {
        this.setState({billingDuration: billingDuration}, function() {
            if (typeof(this.props.billingDurationChanged) === "function") {
                this.props.billingDurationChanged(billingDuration);
            }
        });
    };

    render() {
        return (
            <div className="package-info">
                <div>
                    <span>Package Needed:</span>
                    <span>{this.props.name}</span>
                </div>
                <div>
                    <span>Billing Period:</span>
                    <span>
                        <BillingPeriod
                            billingDuration={this.state.billingDuration}
                            billingDurationChanged={this.billingDurationChanged}
                            disabled={!this.props.billingDurationChangeAllowed}
                            key={this.props.billingDuration}
                        />
                    </span>
                </div>
                <div>
                    <span>Monthly cost:</span>
                    <span dangerouslySetInnerHTML={{__html: this.props.monthlyPrice[this.state.billingDuration]}} />
                </div>
                <div>
                    <span>Due now:</span>
                    <span dangerouslySetInnerHTML={{__html: this.props.price[this.state.billingDuration]}} />
                </div>
            </div>
        );
    }
}

export default PackageInfoComponent;
