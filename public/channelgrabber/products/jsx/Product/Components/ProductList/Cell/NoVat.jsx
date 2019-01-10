import React from 'react';
import layoutSettings from "../Config/layoutSettings";
import styled from 'styled-components';
import stateUtility from "../stateUtility";

const CellContainer = styled.div`
    padding-left: ${layoutSettings.columnPadding};
    padding-right: ${layoutSettings.columnPadding};
`;

class NoVat extends React.Component {
    static defaultProps = {};
    state = {};

    render() {
//        console.log('in noVat render this.props.rowIndex: ' , this.props.rowIndex , this.props);
//
//
//        return (
//            <CellContainer>
//                {this.props.rowIndex} In order to use this feature, please <a href="https://admin.orderhub.io/" title="admin">&nbsp; set your
//                company to VAT registered here </a>.
//            </CellContainer>
//        );

        const {products, rowIndex} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);


//        console.log('in noVat rowIndex', rowIndex);


        let rowId = row.id

        return (
            <div>
            {rowIndex} hh
            </div>
        );

    }
}

export default NoVat;