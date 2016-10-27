define([
    'react',
    'Common/Components/ClickOutside'
], function(
    React,
    ClickOutside
) {
    "use strict";

    var SearchBoxComponent = React.createClass({
        getDefaultProps: function () {
            return {
                placeholder: 'Enter Search Term...',
                results: []
            };
        },
        getInitialState: function () {
            return {
                searchTerm: '',
                selection: '',
                hasFocus: false
            }
        },
        onChange: function (e) {
            this.setState({
                searchTerm: e.target.value
            });
        },
        onBlur: function () {
            console.log('blur');
            if (this.props.onResultSelected) {
                this.props.onResultSelected(this.state.searchTerm);
            }
        },
        onResultSelected: function (selection) {
            this.setState({
                searchTerm: selection.name,
                selection: selection.name,
                hasFocus: false,
            });

            if (this.props.onResultSelected) {
                this.props.onResultSelected(selection.name);
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
                    <li className="react-search-box-result-item" key={index} onClick={function(){this.onResultSelected(result)}.bind(this)}>{result.name}</li>
                )
            }.bind(this));

            if (! results.length || ! this.state.searchTerm.length) {
                return;
            }
            return (
                <ClickOutside onClickOutside={function(){this.setState({hasFocus:false})}.bind(this)}>
                    <div className={"react-search-box-results" + (this.state.hasFocus ? ' active' : '')}>
                        <ul>{results}</ul>
                    </div>
                </ClickOutside>
            );
        },
        render: function () {
            return (
                <div className="react-search-box">
                    <input
                        value={this.state.searchTerm}
                        placeholder={this.props.placeholder}
                        onChange={this.onChange}
                        onBlur={this.onBlur}
                        onClick={function(){this.setState({hasFocus:true})}.bind(this)}/>
                    <span className="sprite-delete-16-black" onClick={function(){this.setState({searchTerm:''})}.bind(this)}></span>
                    {this.getResultsMarkup()}
                </div>
            );
        }
    });

    return SearchBoxComponent;
});
