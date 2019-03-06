import React from 'react';
import styled from 'styled-components';
import ExpandIcon from 'Common/Components/ExpandIcon'

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
    onClick = () => {
        console.log('on click');
        this.props.actions.toggleExpandAll()
    };
    render() {
        let {expand} = this.props;

        return (
            <ExpandIconContainer>
                <ExpandLink onClick={this.onClick}>
                    <ExpandIcon
                        expandStatus={expand.expandAllStatus}
                    />
                </ExpandLink>
            </ExpandIconContainer>
        );
    }
}

export default ProductExpandHeader;