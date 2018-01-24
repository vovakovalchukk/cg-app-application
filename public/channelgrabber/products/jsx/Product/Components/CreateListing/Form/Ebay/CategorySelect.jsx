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
        getOnCategorySelect: function(categoryIndex) {
            return function (selectOption) {
                var newState = Object.assign({}, this.state);
                newState.selectedCategories.splice(categoryIndex);
                newState.selectedCategories[categoryIndex] = selectOption;

                $.ajax({
                    url: '/products/create-listings/ebay/categoryChildren/' + this.props.accountId + '/' + selectOption.value,
                    type: 'GET',
                    success: function (response) {
                        if (response.categories.length == 0) {
                            this.props.onLeafCategorySelected(selectOption.value);
                            return;
                        }
                        newState.categoryMaps.splice(categoryIndex + 1);
                        newState.categoryMaps[categoryIndex + 1] = response.categories;

                        this.setState(newState);
                    }.bind(this)
                });
            }.bind(this);
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
                        <span className={"inputbox-label"}>{index == 0 ? 'Category' : ''}</span>
                        <div className={"order-inputbox-holder"}>
                            <Select
                                options={this.getCategoryOptionsFromCategoryMap(categoryMap)}
                                selectedOption={this.state.selectedCategories[index] ? this.state.selectedCategories[index] : {name: null}}
                                onOptionChange={this.getOnCategorySelect(index)}
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