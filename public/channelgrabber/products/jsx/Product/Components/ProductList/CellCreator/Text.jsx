define([
    'react',
    'fixed-data-table'

], function(
    React,
    FixedDataTable
) {
    "use strict";
    
    const Cell = FixedDataTable.Cell;
    
    let TextCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {};
        },
        render() {
            console.log('in TextCell with this.props: ', this.props);
            
            
            
            
            return (
                <Cell {...this.props}>
                    in text cell
                </Cell>
            );
        }
    });
    
    return TextCell;
});
