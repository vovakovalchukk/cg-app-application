import React from 'react';
import CategoryMaps from 'CategoryMapper/Components/CategoryMaps';
import submitCategoryMapForm from 'CategoryMapper/Service/SubmitCategoryMapForm';
    

    var RootComponent = React.createClass({
        render: function()
        {
            return (
                <CategoryMaps
                    onSubmit={submitCategoryMapForm}
                />
            );
        }
    });

    export default RootComponent;
