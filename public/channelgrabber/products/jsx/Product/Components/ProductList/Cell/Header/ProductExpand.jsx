import React from 'react';
import styled from 'styled-components';

const ArrowWrapper = styled.div`
  display: flex;
  justify-content: center;
  width: 100%;
`;

class ProductExpandHeader extends React.Component {
    static defaultProps = {
    };
    render() {
        let {bulkSelect} = this.props;

        return (
            <ArrowWrapper>
               expand
            </ArrowWrapper>
        );
    }
}

export default ProductExpandHeader;