import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';

import CheckboxStateless from  'Common/Components/Checkbox--stateless';

class BulkSelectHeader extends React.Component {
    static defaultProps = {};
    state = {};

    render() {
        let isSelected = false;

        return (
            <CheckboxStateless
                className={this.props.className}
                onSelect={this.onSelectChange}
                isSelected={isSelected}
            />
        );
    }
}

export default BulkSelectHeader;

