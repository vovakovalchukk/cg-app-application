define([
    'react',
    'Common/Components/Select'
], function(
    React,
    Select
) {
    "use strict";

    var CategorySelectComponent = React.createClass({
        getInitialState: function() {
            return {
                categoryMaps: [

                ],
                selectedCategories: []
            }
        },
        componentDidMount() {
            if (!this.props.rootCategories) {
                return;
            }

            var newState = Object.assign({}, this.state);
            newState.categoryMaps[0] = this.props.rootCategories;
            this.setState(newState);
        },
        componentWillReceiveProps(newProps) {
            if (!newProps.rootCategories) {
                return;
            }
            var newState = Object.assign({}, this.state);
            newState.categoryMaps[0] = newProps.rootCategories;
            this.setState({

            });
        },
        onCategorySelect: function(selectOption) {
            $.ajax({
                url: '/products/create-listings/ebay/categoryChildren/' + this.props.accountId + '/' + selectOption.value,
                type: 'GET',
                success: function (response) {
                   console.log(response);
                }.bind(this)
            });
        },
        getCategoryOptionsFromCategoryMap(categoryMap) {
            var categoryOptions = [];
            for (var externalId in categoryMap) {
                categoryOptions.push({name: categoryMap[externalId], value: externalId});
            }
            return categoryOptions;
        },
        render: function () {
            return <div>
                {this.state.categoryMaps.map(function(categoryMap, index) {
                    return <label>
                        <span className={"inputbox-label"}>Category</span>
                        <div className={"order-inputbox-holder"}>
                            <Select
                                options={this.getCategoryOptionsFromCategoryMap(categoryMap)}
                                selectedOption={{}}
                                onOptionChange={this.onCategorySelect}
                                autoSelectFirst={false}
                            />
                        </div>
                    </label>
                }.bind(this))}
            </div>
        }
    });

    return CategorySelectComponent;

});