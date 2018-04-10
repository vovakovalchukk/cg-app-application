define([
	'react',
	'redux-form'
], function (
	React,
	reduxForm
) {
		var Field = reduxForm.Field;
		var Form = reduxForm.Form;

		var createFormComponent = React.createClass({
			getDefaultProps: function () {
				return {
					handleSubmit: null
				};
			},
			render: function () {
				return (
					<Form id="create-product-form" onSubmit={this.props.handleSubmit}>
						<div className={"order-form half"}>
							<label>
								<span className={"inputbox-label"}>Title:</span>
								<div className={"order-inputbox-holder"}>
									<Field type="text" name="title" component="input" />
								</div>
							</label>
						</div>
					</Form>
				);
			}
		})

		return reduxForm.reduxForm({
			form: 'createProductForm'
		})(createFormComponent);
	});
