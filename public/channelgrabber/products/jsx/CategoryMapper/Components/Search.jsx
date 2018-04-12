define([
    'react'
], function(
    React
) {
    "use strict";

    var SearchComponent = React.createClass({
        getInitialState: function() {
            return {
                value: ''
            }
        },
        getDefaultProps: function() {
            return {
                onSubmit: function () {},
                disabled: false
            }
        },
        onChange: function (event) {
            this.setState({
                value: event.target.value
            });
        },
        onSubmit: function(event) {
            event.preventDefault();
            this.props.onSubmit(this.state.value);
        },
        render: function() {
            return (
                <span className={"heading-large"}>
                    <label>Existing category maps</label>
                    <form
                        className="search-form"
                        name="search"
                        onSubmit={this.onSubmit}
                    >
                        <label>
                            <div className={"order-inputbox-holder"}>
                                <input
                                    type="text"
                                    placeholder="Search..."
                                    onChange={this.onChange}
                                    value={this.state.value}
                                    disabled={this.props.disabled}
                                />
                            </div>
                        </label>
                    </form>
                </span>
            );
        }
    });

    return SearchComponent;
});