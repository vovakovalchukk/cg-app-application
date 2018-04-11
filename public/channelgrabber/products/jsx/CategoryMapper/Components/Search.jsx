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
                <form
                    name="search"
                    onSubmit={this.onSubmit}
                >
                    <div className={"order-form half product-container category-map-container"}>
                        <div>
                            <label>
                                <div className={"order-inputbox-holder"}>
                                    <input
                                        type="text"
                                        placeholder="Search..."
                                        onChange={this.onChange}
                                        value={this.state.value}
                                    />
                                </div>
                            </label>
                        </div>
                    </div>
                </form>
            );
        }
    });

    return SearchComponent;
});