define(['react', 'SetupWizard/Component/Payment/BillingPeriod'], function(React, BillingPeriod) {
    "use strict";

    var PackageInfoComponent = React.createClass({
        getDefaultProps: function() {
            return {
                id: null,
                name: null,
                band: null,
                monthlyPrice: null,
                price: null,
                orderVolume: null
            };
        },
        getInitialState: function() {
            return {
                billingDuration: 1
            };
        },
        render: function() {
            return (
                <div className="package-info">
                    <div>
                        <span>Package:</span>
                        <span>{this.props.band.replace(/\s+\(USA\)$/, '')}</span>
                        <span>
                        <a target="_blank" href="https://www.channelgrabber.com/pricing">What do I get?</a>
                    </span>
                    </div>
                    <div>
                        <span>Billing Period:</span>
                        <span>
                            <BillingPeriod
                                billingDuration={this.state.billingDuration}
                                billingDurationChanged={billingDuration => this.setState({billingDuration: billingDuration})}
                            />
                        </span>
                    </div>
                    <div>
                        <span>Monthly cost:</span>
                        <span>{this.props.monthlyPrice[this.state.billingDuration]}</span>
                    </div>
                    <div>
                        <span>Due now:</span>
                        <span>{this.props.price[this.state.billingDuration]}</span>
                    </div>
                </div>
            );
        }
    });

    return PackageInfoComponent;
});