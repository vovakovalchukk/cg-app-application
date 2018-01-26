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
            this.saveNewRootCategoriesToState(this.props.rootCategories);
        },
        componentWillReceiveProps(newProps) {
            if (newProps.rootCategories != this.props.rootCategories) {
                this.saveNewRootCategoriesToState(newProps.rootCategories);
            }
        },
        saveNewRootCategoriesToState: function (rootCategories) {
            if (!rootCategories) {
                return;
            }
            var newState = this.getInitialState();
            newState.categoryMaps = [rootCategories];

            this.setState(newState);
        },
        removeChildElementsAndSelections: function(object, currentIndex) {
            object.splice(currentIndex);
            return object;
        },
        getOnCategorySelect: function(categoryIndex) {
            return function (selectOption) {
                var newState = JSON.parse(JSON.stringify(this.state));

                newState.categoryMaps = this.removeChildElementsAndSelections(newState.categoryMaps, categoryIndex + 1);
                newState.selectedCategories = this.removeChildElementsAndSelections(
                    newState.selectedCategories,
                    categoryIndex
                );
                newState.selectedCategories[categoryIndex] = selectOption;

                this.setState(newState);
                this.props.onLeafCategorySelected(null);
                this.fetchAndSetChildCategories(selectOption.value, categoryIndex, newState);
            }.bind(this);
        },
        fetchAndSetChildCategories: function (selectedCategoryId, categoryIndex, previouslySetState) {
            $.ajax({
                context: this,
                url: '/products/create-listings/ebay/categoryChildren/' + this.props.accountId + '/' + selectedCategoryId,
                type: 'GET',
                success: function (response) {
                    if (response.categories.length == 0) {
                        this.props.onLeafCategorySelected(selectedCategoryId);
                        return;
                    }
                    previouslySetState.categoryMaps[categoryIndex + 1] = response.categories;

                    this.setState(previouslySetState);
                }
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