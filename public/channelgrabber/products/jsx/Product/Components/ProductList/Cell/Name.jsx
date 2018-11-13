import React from 'react';
import Clipboard from 'Clipboard';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import styled from 'styled-components';
import LinesEllipsis from 'react-lines-ellipsis'
import layoutSettings from 'Product/Components/ProductList/Config/layoutSettings';

let NameContainer = styled.div`
    display:flex;
    align-items:center;
    height:100%;
    padding-left: ${layoutSettings.columnPadding};
    padding-right: ${layoutSettings.columnPadding};
`;

class NameCell extends React.Component {
    static defaultProps = {};
    state = {};

    getVariationName = (row) => {
        return Object.keys(row.attributeValues).map((key) => {
            return (
                <div>{key}: {row.attributeValues[key]}&nbsp;</div>
            );
        });
    };
    getUniqueClassName = () => {
        return 'js-' + this.props.columnKey + '-' + this.props.rowIndex;
    };
    getClassNames = () => {
        return this.props.className + ' ' + this.getUniqueClassName();
    };
    componentDidMount() {
        new Clipboard('div.' + this.getUniqueClassName(), [], 'data-copy');
    };
    render() {
        const {products, rowIndex} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);
        const isVariation = stateUtility.isVariation(row);
        let name = isVariation ? this.getVariationName(row) : row['name'];

        name = "sdfsoidjfsodijgdofgjdofgijd fdjfgoidjgod odifjgdofijg iojsoijfsoij nwe kjn kosidjfosidjfos oiawjoijwof nsk jdfskbiuq oqiwo qenqwkejnqkwe qiw oijs ois ofjsodi osif jo"

        return (
            <NameContainer>
                <LinesEllipsis
                    text={name}
                    maxLine='2'
                    ellipsis='...'
                    trimRight
                    basedOn='letters'
                    title={name}
                />
            </NameContainer>
        );

        //        return (
//            <div {...this.props} className={this.getClassNames()} data-copy={name} title={name}>
//                <NameText id ="in the name">{name}</NameText>
//            </div>
//        );
    }
}

export default NameCell;