define([
    'react',
    'Redux/Containers/Todo/FilterLink'
], function(
    React,
    FilterLinkContainer
) {
    "use strict";

    var FilterLinksComponent = React.createClass({
        render: function()
        {
            return (
                <p>
                    Show:
                    {" "}
                    <FilterLinkContainer filter="SHOW_ALL">All</FilterLinkContainer>
                    {", "}
                    <FilterLinkContainer filter="SHOW_ACTIVE">Active</FilterLinkContainer>
                    {", "}
                    <FilterLinkContainer filter="SHOW_COMPLETED">Completed</FilterLinkContainer>
                </p>
            );
        }
    });

    return FilterLinksComponent;
});