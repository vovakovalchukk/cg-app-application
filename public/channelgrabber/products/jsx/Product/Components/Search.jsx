import React from 'react';
import Button from 'Common/Components/Button';
import styled from 'styled-components';

"use strict";

const SearchField = styled.div`
        width: 250px;
    `;

class SearchComponent extends React.Component {
    static defaultProps = {
        initialSearchTerm: ''
    };

    state = {
        searchTerm: this.props.initialSearchTerm
    };

    searchTermUpdate = (e) => {
        this.setState({
            searchTerm: e.target.value
        });
    };

    searchButtonPressed = () => {
        this.props.submitCallback(this.state.searchTerm);
    };

    onKeyPress = (e) => {
        if (e.key === 'Enter') {
            this.searchButtonPressed();
        }
    };

    componentDidMount = () => {
        if (this.props.initialSearchTerm) {
            this.searchButtonPressed();
        }
    };

    render() {
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
}

export default SearchComponent;