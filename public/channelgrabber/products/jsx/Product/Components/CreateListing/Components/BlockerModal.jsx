define([
    'react',
], function(
    React,
) {
    "use strict";
    var BlockerModal = React.createClass({
        getDefaultProps: function () {
            return {
                headerText:'',
                contentParagraphs:[],
                buttonText:'',
                footerJsx:'',
                buttonOnClick:null
            }
        },
        render: function() {
            console.log('in BlockerModal props : ' , this.props);
            return(
                <div className={'c-blocker-modal'}>
                    <div className={'c-blocker-modal__overlay'}>
                        <div className={'c-blocker-modal__container'}>
                            <div className={'c-blocker-modal__heading-container'}>
                                {this.props.headerText}
                            </div>
                            <div className={'c-blocker-modal__content-container'}>
                                <div className="c-blocker-modal__text-content">
                                    {this.props.contentJsx}
                                </div>
                                <button className={'c-blocker-modal__cta-button'} onClick={this.props.buttonOnClick}>
                                    {this.props.buttonText}
                                </button>
                                <div className={'c-blocker-modal__footer-text'}>
                                    {this.props.footerJsx}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            );
        }
    });
    return BlockerModal;
});