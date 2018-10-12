import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import stateFilters from 'Product/Components/CreateProduct/functions/stateFilters';
import Component from './Component';
import ActionCreators from './ActionCreators';
    
    const mapStateToProps = function(state, ownProps) {
        var filteredState = stateFilters.filterFields(2, state.variationsTable);
        return {
            fields: filteredState.fields,
            rows: state.variationsTable.variations,
            values: state.form.createProductForm.values,
            uploadedImages: state.uploadedImages,
            classNames: ownProps.classNames,
            cells: state.variationsTable.cells
        }
    };
    const mapDispatchToProps = function(dispatch) {
        return bindActionCreators(ActionCreators, dispatch);
    };
    var Connector = connect(mapStateToProps, mapDispatchToProps);
    export default Connector(Component);
