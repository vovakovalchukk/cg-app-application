define([
    'react'
], function(
    React
) {
    "use strict";
    var BlockerModal = React.createClass({
        getDefaultProps: function() {
            return {
                headerText: '',
                contentParagraphs: [],
                buttonText: '',
                footerJsx: '',
                buttonOnClick: null
            }
        },
        render: function() {
            return (
                <div className={'o-modal-area'}>
                    <div className={'o-modal-area__overlay'}>
                        <div className={'o-modal-area__content-container'}>
                            <div className={'c-card c-card--centered c-card--large'}>

                                <div className={'c-card__heading-container'}>
                                    {this.props.headerText}
                                </div>
                                <div className={'c-card__content-container'}>
                                    <div className="c-card__text-content">
                                        {this.props.contentJsx}
                                    </div>
                                    <button className={'c-card__cta-button'}
                                            onClick={this.props.buttonOnClick}>
                                        {this.props.buttonText}
                                    </button>
                                    <div className={'c-card__footer-text'}>
                                        {this.props.footerJsx}
                                    </div>
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