import React from 'react';
import styled from 'styled-components';
import ExpandIcon from 'Common/Components/ExpandIcon'
import constants from 'Product/Components/ProductList/Config/constants';
import loadingIndicatorFactory from 'element/loadingIndicator';
import stateUtility from "../../stateUtility";

const loadingIndicator = loadingIndicatorFactory.getIndicator();

const {EXPAND_STATUSES} = constants;

const ExpandIconContainer = styled.div`
  display: flex;
  justify-content: center;
  width: 100%;
`;
const ExpandLink = styled.a`
    user-select: none;
`;

class ProductExpandHeader extends React.Component {
    static defaultProps = {};
    static CONFIRMATION_POPUP_NAME = 'ExpandVariationsConfirmation';
    static MAX_VARIATIONS_COUNT = 250;

    onClick = () => {
        this.props.actions.toggleExpandAll()
    };

    render() {
        let {expand} = this.props;

        return (
            <ExpandIconContainer>
                <ExpandLink onClick={this.onClick}>
                    <ExpandIcon
                        expandStatus={expand.expandAllStatus}
                        indicator={loadingIndicator}
                        EXPAND_STATUSES={EXPAND_STATUSES}
                        iconColor={'white'}
                    />
                </ExpandLink>
            </ExpandIconContainer>
        );
    }
}

export default ProductExpandHeader;