define([
    'react',
    'Common/Components/Select',
], function(
    React,
    Select
) {
    "use strict";

    return React.createClass({
        getDefaultProps: function() {
            return {
                accountId: null
            }
        },
        getInitialState: function() {
            return {
                categories: {}
            }
        },
        fetchCategories: function() {
            $.get('/products/create-listings/' + this.props.accountId + '/channel-specific-field-values', function(data) {
                if (data.error) {
                    console.log(data.error);
                    return;
                }

                this.state.categories = data.categories;
            }.bind(this));
        },
        getSelectOptions: function() {
            var options = [];
            $.each(this.state.categories, function(id, name) {
                options.push({name: id, value: name})
            });
            return options;
        },
        render: function() {
            this.fetchCategories();
            return <div>
                <label>
                    <span className={"inputbox-label"}>Category</span>
                    <div className={"order-inputbox-holder"}>
                        <Select
                            name="shopify-category"
                            options={this.getSelectOptions()}
                        />
                    </div>
                </label>
            </div>;
        }
    });
});
