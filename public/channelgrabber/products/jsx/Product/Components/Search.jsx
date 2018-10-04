import React from 'react';
import Button from 'Common/Components/Button';
import styled from 'styled-components';

"use strict";

const SearchField = styled.div`
        width: 250px;
    `;

var SearchComponent = React.createClass({
    getDefaultProps: function() {
        return {
            initialSearchTerm: ''
        }
    },
    getInitialState: function() {
        return {
            searchTerm: this.props.initialSearchTerm
        }
    },
    searchTermUpdate: function(e) {
        this.setState({
            searchTerm: e.target.value
        });
    },
    searchButtonPressed: function() {
        this.props.submitCallback(this.state.searchTerm);
    },
    onKeyPress: function(e) {
        if (e.key === 'Enter') {
            this.searchButtonPressed();
        }
    },
    render: function() {
        return (
            <div id="search-box-wrapper">
                <div id="searchUIContainer">
                    <SearchField className="med-element search-field">
                        <label htmlFor="filter-search-field">
                            <div className="sprite-search-18-black"></div>
                        </label>
                        <input name="filter-search-field" value={this.state.searchTerm} type="text"
                               className="search-field-input" onChange={this.searchTermUpdate}
                               onKeyPress={this.onKeyPress}/>
                    </SearchField>
                </div>
                <div id="searchBtn">
                    <Button text='Search' onClick={this.searchButtonPressed}/>
                </div>
            </div>
        );
    }
});

export default SearchComponent;