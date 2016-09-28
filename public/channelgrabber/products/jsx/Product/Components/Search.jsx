define([
    'react',
    'Product/Components/Button'
], function(
    React,
    Button
) {
    "use strict";

    var SearchComponent = React.createClass({
        getInitialState: function() {
            return {
                searchTerm: ""
            }
        },
        searchTermUpdate: function (e) {
            this.setState({
                searchTerm: e.target.value
            });
        },
        searchButtonPressed: function () {
            this.props.submitCallback(this.state.searchTerm);
        },
        onKeyPress: function (e) {
            if (e.key === 'Enter') {
                this.searchButtonPressed();
            }
        },
        render: function() {
            return (
                <div id="search-box-wrapper">
                    <div id="searchUIContainer">
                        <div className="med-element search-field">
                            <label htmlFor="filter-search-field">
                                <div className="sprite-search-18-black"></div>
                            </label>
                            <input name="filter-search-field" value={this.state.searchTerm} type="text" className="search-field-input" onChange={this.searchTermUpdate} onKeyPress={this.onKeyPress}/>
                        </div>
                    </div>
                    <div id="searchBtn">
                        <Button text='Search' onClick={this.searchButtonPressed}/>
                    </div>
                </div>
            );
        }
    });

    return SearchComponent;
});