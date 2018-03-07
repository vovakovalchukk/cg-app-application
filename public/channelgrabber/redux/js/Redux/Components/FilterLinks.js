define(['react', 'Redux/Containers/FilterLink'], function (React, FilterLinkContainer) {
    "use strict";

    var FilterLinksComponent = React.createClass({
        displayName: 'FilterLinksComponent',

        render: function () {
            return React.createElement(
                'p',
                null,
                'Show:',
                " ",
                React.createElement(
                    FilterLinkContainer,
                    { filter: 'SHOW_ALL' },
                    'All'
                ),
                ", ",
                React.createElement(
                    FilterLinkContainer,
                    { filter: 'SHOW_ACTIVE' },
                    'Active'
                ),
                ", ",
                React.createElement(
                    FilterLinkContainer,
                    { filter: 'SHOW_COMPLETED' },
                    'Completed'
                )
            );
        }
    });

    return FilterLinksComponent;
});
