define([
    'react',
    'Product/Components/Link',
    'Product/Components/ProductList/stateFilters'

], function(
    React,
    Link,
    stateFilters

) {
    "use strict";
    
    let LinkCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {};
        },
        render() {
            const rowData = stateFilters.getRowData(this.props.products, this.props.rowIndex)
    
            return (
                <div >
                    <Link />
                </div>
            );
        }
    });
    
    return LinkCell;
});
