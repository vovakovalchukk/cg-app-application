import React from 'react';

const TemplateManager = (props) => {
    const {match} = props;
    const {params} = match;

    return (
        <div>
            <h1>Templates</h1>
            <p className='u-clear-both'>

                some stuff in here
            </p>
        </div>
    );
};

export default TemplateManager;
