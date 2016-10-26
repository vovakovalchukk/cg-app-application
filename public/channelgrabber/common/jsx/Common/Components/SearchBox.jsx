define([
    'react'
], function(
    React
) {
    "use strict";

    var SearchBoxComponent = React.createClass({
        getDefaultProps: function () {
            return {
                placeholder: 'Enter Search Term...',
                selection: '',
                results: []
            };
        },
        getInitialState: function () {
            return {
                searchTerm: '',
                hasFocus: false
            }
        },
        onChange: function (e) {
            this.setState({
                searchTerm: e.target.value
            });
        },
        onResultSelected: function (selection) {
            this.setState({
                searchTerm: selection.name,
                selection: selection.name,
                hasFocus: false,
            });

            if (this.props.onResultSelected) {
                this.props.onResultSelected(selection);
            }
        },
        filterBySearchTerm: function(result) {
            if (result.name.toUpperCase().includes(this.state.searchTerm.toUpperCase())) {
                return true;
            }
        },
        getResultsMarkup: function () {
            var results = this.props.results.filter(this.filterBySearchTerm).map(function(result, index) {
                return (
                    <li className="react-search-box-result-item" key={index} onClick={() => this.onResultSelected(result)}>{result.name}</li>
                )
            }.bind(this));

            if (! results.length || ! this.state.searchTerm.length) {
                return;
            }
            return (
                <div className={"react-search-box-results" + (this.state.hasFocus ? ' active' : '')}>
                    <ul>{results}</ul>
                </div>
            );
        },
        render: function () {
            return (
                <div className="react-search-box">
                    <input value={this.state.searchTerm} placeholder={this.props.placeholder} onChange={this.onChange} onClick={() => this.setState({hasFocus:true})}/>
                    {this.getResultsMarkup()}
                </div>
            );
        }
    });

    return SearchBoxComponent;
});
