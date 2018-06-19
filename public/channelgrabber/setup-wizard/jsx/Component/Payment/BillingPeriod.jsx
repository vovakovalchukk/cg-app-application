define(['react'], function(React) {
    "use strict";

    var BillingPeriodComponent = React.createClass({
        getInitialState: function() {
            return {
                checked: false
            };
        },
        render: function() {
            return (
                <span className="billingDuration">
                    <input type="checkbox" checked={this.state.checked}/>
                    <label onClick={event => this.setState({checked: !this.state.checked})} />
                </span>
            );
        }
    });

    return BillingPeriodComponent;
});