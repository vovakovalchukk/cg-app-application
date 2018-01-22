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
                categoryLists: [
                    ["one", "two", "three"],
                    ["four", "five", "six"]
                ],
                selectedCategories: []
            }
        },
        onCategorySelect: function(event) {
            console.log(event);
        },
        render: function () {
            console.log(this.state.categoryLists);
            return <div>
                {this.state.categoryLists.map(function(categoryList, index) {
                    return <label>
                        <span className={"inputbox-label"}>Category</span>
                        <div className={"order-inputbox-holder"}>
                            <Select
                                options={categoryList}
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