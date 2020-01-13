import React, {useEffect} from 'react';

const ShadowDomDiv = (props) => {
    const shadowRef = React.createRef();

    useEffect(() => {
        const shadowChild = shadowRef.current.querySelector('div');
        if ( shadowChild.shadowRoot === null) {
            shadowChild.attachShadow({
                mode: 'open'
            });
        }
        shadowChild.shadowRoot.innerHTML = props.body;
    });

    return (
        <div
            ref={shadowRef}
            className={`u-display-flex`}
        >
            <div />
        </div>
    )
};

export default ShadowDomDiv;