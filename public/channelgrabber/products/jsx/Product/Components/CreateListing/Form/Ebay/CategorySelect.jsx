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
        getDefaultProps: function() {
            return {
                title: null
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

                if (selectOption.disabled) {
                    this.setState(newState);
                    n.error('The selected category <b>' + selectOption.name + '</b> doesn\'t support varations. Please select another category.');
                    return;
                }

                newState.selectedCategories[categoryIndex] = selectOption;

                this.setState(newState);
                this.props.onLeafCategorySelected(null);
                this.fetchAndSetChildCategories(selectOption.value, categoryIndex, newState);
            }.bind(this);
        },
        fetchAndSetChildCategories: function (selectedCategoryId, categoryIndex, previouslySetState) {
            $.ajax({
                context: this,
                url: '/products/create-listings/' + this.props.accountId + '/category-children/' + selectedCategoryId,
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
            var categoryOptions = [], disabled, category;
            for (var externalId in categoryMap) {
                category = categoryMap[externalId];
                disabled = false;
                if (category.hasOwnProperty('variations')) {
                    disabled = !category.variations;
                }
                categoryOptions.push({
                    name: category.title,
                    value: externalId,
                    disabled: disabled
                });
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
                                title={index == 0 ? this.props.title : null}
                            />
                        </div>
                    </label>
                }.bind(this))}
            </div>
        }
    });

    return CategorySelectComponent;

});