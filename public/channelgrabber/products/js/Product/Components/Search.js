define(['react', 'Product/Components/Button'], function (React, Button) {
    "use strict";

    var SearchComponent = React.createClass({
        displayName: 'SearchComponent',

        getInitialState: function () {
            return {
                searchTerm: ""
            };
        },
        searchTermUpdate: function (e) {
            this.setState({
                searchTerm: e.target.value
            });
        },
        searchButtonPressed: function () {
            this.props.submitCallback(this.state.searchTerm);
        },
        render: function () {
            return React.createElement(
                'div',
                { id: 'search-box-wrapper' },
                React.createElement(
                    'div',
                    { id: 'searchUIContainer' },
                    React.createElement(
                        'div',
                        { className: 'med-element search-field' },
                        React.createElement(
                            'label',
                            { htmlFor: 'filter-search-field' },
                            React.createElement('div', { className: 'sprite-search-18-black' })
                        ),
                        React.createElement('input', { name: 'filter-search-field', value: this.state.searchTerm, type: 'text', className: 'search-field-input', onChange: this.searchTermUpdate })
                    )
                ),
                React.createElement(
                    'div',
                    { id: 'searchBtn' },
                    React.createElement(Button, { text: 'Search', onClick: this.searchButtonPressed })
                )
            );
        }
    });

    return SearchComponent;
});
