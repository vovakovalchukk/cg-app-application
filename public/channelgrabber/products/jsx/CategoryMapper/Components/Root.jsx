import React from 'react';
import CategoryMaps from 'CategoryMapper/Components/CategoryMaps';
import submitCategoryMapForm from 'CategoryMapper/Service/SubmitCategoryMapForm';


class RootComponent extends React.Component {
    render() {
        return (
            <CategoryMaps
                onSubmit={submitCategoryMapForm}
            />
        );
    }
}

export default RootComponent;
