import React from 'react';


class BillingPeriodComponent extends React.Component {
    static defaultProps = {
        billingDuration: null,
        billingDurationChanged: null,
        disabled: false
    };

    state = {
        checked: (this.props.billingDuration === 12)
    };

    componentDidUpdate(prevProps) {
        var billingDuration = this.state.checked ? 12 : 1;
        if (prevProps.billingDuration === billingDuration) {
            return;
        }

        this.props.billingDuration = billingDuration;
        if (typeof(this.props.billingDurationChanged) === "function") {
            this.props.billingDurationChanged(billingDuration);
        }
    }

    render() {
        return (
            <span className="billingDuration">
                <span>Monthly</span>
                <input type="checkbox" checked={this.state.checked} disabled={this.props.disabled}/>
                <span className="label" onClick={event => {if (this.props.disabled) { return; } this.setState({checked: !this.state.checked})}} />
                <span>Annually (2 Months Free)</span>
            </span>
        );
    }
}

export default BillingPeriodComponent;
