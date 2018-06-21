define(['react'], function(React) {
    "use strict";

    var BillingPeriodComponent = React.createClass({
        getDefaultProps: function() {
            return {
                billingDuration: null,
                billingDurationChanged: null
            };
        },
        getInitialState: function() {
            return {
                checked: (this.props.billingDuration === 12)
            };
        },
        componentDidUpdate: function(props) {
            var billingDuration = this.state.checked ? 12 : 1;
            if (props.billingDuration === billingDuration) {
                return;
            }

            this.props.billingDuration = billingDuration;
            if (typeof(this.props.billingDurationChanged) === "function") {
                this.props.billingDurationChanged(billingDuration);
            }
        },
        render: function() {
            return (
                <span className="billingDuration">
                    <span>Monthly</span>
                    <input type="checkbox" checked={this.state.checked}/>
                    <span className="label" onClick={event => this.setState({checked: !this.state.checked})} />
                    <span>Annually (2 Months Free)</span>
                </span>
            );
        }
    });

    return BillingPeriodComponent;
});