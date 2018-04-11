define([
    'react'
], function(
    React
) {
    "use strict";

    var SearchComponent = React.createClass({
        getDefaultProps: function() {
            return {
                value: '',
                onChange: function () {},
                onSubmit: function () {},
            }
        },
        onChange: function (event) {
            this.props.onChange(event.target.value);
        },
        onSubmit: function(event) {
            event.preventDefault();
            this.props.onSubmit();
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
                                        value={this.props.value}
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