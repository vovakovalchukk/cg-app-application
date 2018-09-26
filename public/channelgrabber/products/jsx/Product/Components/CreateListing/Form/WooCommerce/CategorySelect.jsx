import React from 'react';
import Select from 'Common/Components/Select';
import RefreshIcon from 'Common/Components/RefreshIcon';
    

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
            var newState = {
                categoryMaps: [rootCategories]
            };

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
                this.props.onLeafCategorySelected(selectOption.value);
                this.fetchAndSetChildCategories(selectOption.value, categoryIndex, newState);
            }.bind(this);
        },
        fetchAndSetChildCategories: function (selectedCategoryId, categoryIndex, previouslySetState) {
            $.ajax({
                context: this,
                url: '/products/create-listings/' + this.props.accountId + '/category-children/' + selectedCategoryId,
                type: 'GET',
                success: function (response) {
                    if (response.categories && response.categories.length === 0) {
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
                categoryOptions.push({name: categoryMap[externalId].title, value: externalId});
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
                        {index == 0 && <RefreshIcon
                            disabled={this.props.refreshCategoriesDisabled}
                            onClick={this.props.refreshCategories}
                        />}
                    </label>
                }.bind(this))}
            </div>
        }
    });

    export default CategorySelectComponent;

