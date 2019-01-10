import React from 'react';
import layoutSettings from "../Config/layoutSettings";
import styled from 'styled-components';
import stateUtility from "../stateUtility";

const CellContainer = styled.div`
    padding-left: ${layoutSettings.columnPadding};
    padding-right: ${layoutSettings.columnPadding};
    display: flex;
    align-items: center;
    height: 100%;
`;

class NoVat extends React.Component {
    static defaultProps = {};
    state = {};

    render() {
        return (
            <CellContainer>
                In order to use this feature, please <a href="https://admin.orderhub.io/" title="admin">&nbsp; set your
                company to VAT registered here </a>.
            </CellContainer>
        );
    }
}

export default NoVat;