import React from 'react';
import styled from 'styled-components';

import CheckboxStateless from  'Common/Components/Checkbox--stateless';

const CheckboxWrapper = styled.div`
  display: flex;
  justify-content: center;
  width: 100%;
`;

class BulkSelectHeader extends React.Component {
    static defaultProps = {};
    state = {};

    onSelect = () => {
        this.props.actions.toggleSelectAllBulkSelect();
    };
    render() {
        let isSelected = false;

        return (
            <CheckboxWrapper>
                <CheckboxStateless
                    className={this.props.className}
                    onSelect={this.onSelect}
                    isSelected={isSelected}
                />
            </CheckboxWrapper>
        );
    }
}

export default BulkSelectHeader;